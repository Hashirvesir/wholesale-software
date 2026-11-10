/**
 * Offline Sync Manager for Wholesaler Software
 * Handles offline caching, queues, connectivity detection, and auto-sync.
 */
(function (window) {
    'use strict';

    const STORAGE_KEY_SALES = 'invenco_offline_sales_queue';
    const STORAGE_KEY_PAYMENTS = 'invenco_offline_payments_queue';
    const STORAGE_KEY_MASTER = 'invenco_offline_master_data';
    const STORAGE_KEY_LAST_SYNC = 'invenco_offline_last_sync';

    class OfflineSyncManager {
        constructor() {
            this.isOnline = navigator.onLine;
            this.isSyncing = false;
            this.lastSyncTime = localStorage.getItem(STORAGE_KEY_LAST_SYNC) || null;
            this.salesQueue = this.loadQueue(STORAGE_KEY_SALES);
            this.paymentsQueue = this.loadQueue(STORAGE_KEY_PAYMENTS);
            this.masterData = this.loadMasterData();
            this.pingTimer = null;

            this.init();
        }

        loadQueue(key) {
            try {
                const data = localStorage.getItem(key);
                return data ? JSON.parse(data) : [];
            } catch (e) {
                console.error('Error loading queue:', key, e);
                return [];
            }
        }

        saveQueue(key, queue) {
            try {
                localStorage.setItem(key, JSON.stringify(queue));
            } catch (e) {
                console.error('Error saving queue:', key, e);
            }
            this.dispatchStatusEvent();
        }

        loadMasterData() {
            try {
                const data = localStorage.getItem(STORAGE_KEY_MASTER);
                return data ? JSON.parse(data) : null;
            } catch (e) {
                return null;
            }
        }

        init() {
            // 1. Register Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((reg) => {
                            // Service worker registered
                        })
                        .catch((err) => {
                            console.warn('Service worker registration failed:', err);
                        });
                });
            }

            // 2. Network status event listeners
            window.addEventListener('online', () => {
                this.checkConnection(true);
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.dispatchStatusEvent();
            });

            // 3. Periodic real ping check (every 15s)
            this.checkConnection(false);
            this.pingTimer = setInterval(() => {
                this.checkConnection(false);
            }, 15000);

            // 4. Initial Master Data sync if online
            if (this.isOnline && !this.masterData) {
                setTimeout(() => this.fetchMasterData(), 1000);
            }
        }

        async checkConnection(triggerSyncIfOnline = false) {
            if (!navigator.onLine) {
                this.isOnline = false;
                this.dispatchStatusEvent();
                return false;
            }

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 4000);

                const res = await fetch('/api/offline/ping', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                const wasOffline = !this.isOnline;
                this.isOnline = res.ok;

                if (this.isOnline && (wasOffline || triggerSyncIfOnline || this.getPendingCount() > 0)) {
                    this.syncAll();
                }
            } catch (e) {
                this.isOnline = false;
            }

            this.dispatchStatusEvent();
            return this.isOnline;
        }

        getPendingCount() {
            return this.salesQueue.length + this.paymentsQueue.length;
        }

        dispatchStatusEvent() {
            const detail = {
                isOnline: this.isOnline,
                isSyncing: this.isSyncing,
                pendingCount: this.getPendingCount(),
                salesCount: this.salesQueue.length,
                paymentsCount: this.paymentsQueue.length,
                lastSyncTime: this.lastSyncTime
            };

            window.dispatchEvent(new CustomEvent('offline-status-changed', { detail }));
        }

        async fetchMasterData() {
            if (!this.isOnline) return false;

            try {
                const res = await fetch('/api/offline/master-data', {
                    headers: { 'Accept': 'application/json' }
                });

                if (res.ok) {
                    const data = await res.json();
                    this.masterData = data;
                    localStorage.setItem(STORAGE_KEY_MASTER, JSON.stringify(data));
                    this.lastSyncTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    localStorage.setItem(STORAGE_KEY_LAST_SYNC, this.lastSyncTime);
                    this.dispatchStatusEvent();
                    return true;
                }
            } catch (e) {
                console.warn('Failed to fetch master data:', e);
            }
            return false;
        }

        /**
         * Enqueue an offline sale.
         */
        saveOfflineSale(saleData) {
            const offlineId = 'OFFLINE-SALE-' + Date.now() + '-' + Math.random().toString(36).substring(2, 7);
            const enriched = {
                offline_id: offlineId,
                client_created_at: new Date().toISOString(),
                ...saleData
            };

            this.salesQueue.push(enriched);
            this.saveQueue(STORAGE_KEY_SALES, this.salesQueue);

            // Attempt immediate sync if connection is present
            if (this.isOnline) {
                this.syncAll();
            }

            return enriched;
        }

        /**
         * Enqueue an offline payment.
         */
        saveOfflinePayment(paymentData) {
            const offlineId = 'OFFLINE-PAY-' + Date.now() + '-' + Math.random().toString(36).substring(2, 7);
            const enriched = {
                offline_id: offlineId,
                client_created_at: new Date().toISOString(),
                ...paymentData
            };

            this.paymentsQueue.push(enriched);
            this.saveQueue(STORAGE_KEY_PAYMENTS, this.paymentsQueue);

            if (this.isOnline) {
                this.syncAll();
            }

            return enriched;
        }

        /**
         * Synchronize pending items to server.
         */
        async syncAll() {
            if (this.isSyncing) return;
            if (this.getPendingCount() === 0) return;
            if (!this.isOnline) return;

            this.isSyncing = true;
            this.dispatchStatusEvent();

            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenEl ? tokenEl.getAttribute('content') : '';

            const payload = {
                sales: this.salesQueue,
                payments: this.paymentsQueue
            };

            try {
                const res = await fetch('/api/offline/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const result = await res.json();

                    // Remove successfully synced sales
                    if (result.synced_sales && result.synced_sales.length > 0) {
                        const syncedIds = new Set(result.synced_sales.map(s => s.offline_id));
                        this.salesQueue = this.salesQueue.filter(s => !syncedIds.has(s.offline_id));
                        this.saveQueue(STORAGE_KEY_SALES, this.salesQueue);
                    }

                    // Remove successfully synced payments
                    if (result.synced_payments && result.synced_payments.length > 0) {
                        const syncedIds = new Set(result.synced_payments.map(p => p.offline_id));
                        this.paymentsQueue = this.paymentsQueue.filter(p => !syncedIds.has(p.offline_id));
                        this.saveQueue(STORAGE_KEY_PAYMENTS, this.paymentsQueue);
                    }

                    this.lastSyncTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    localStorage.setItem(STORAGE_KEY_LAST_SYNC, this.lastSyncTime);

                    // Show success notification if items were synced
                    const totalSynced = (result.synced_sales ? result.synced_sales.length : 0) +
                                        (result.synced_payments ? result.synced_payments.length : 0);

                    if (totalSynced > 0) {
                        this.showSyncToast(`Synced ${totalSynced} offline transaction(s) to server successfully!`);
                        // Refresh cached master data
                        this.fetchMasterData();
                    }

                    window.dispatchEvent(new CustomEvent('offline-sync-completed', { detail: result }));
                } else {
                    console.error('Offline sync failed HTTP status:', res.status);
                }
            } catch (err) {
                console.error('Offline sync network error:', err);
            } finally {
                this.isSyncing = false;
                this.dispatchStatusEvent();
            }
        }

        showSyncToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 right-5 z-[9999] bg-emerald-600 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs font-black flex items-center gap-3 animate-bounce';
            toast.innerHTML = `
                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    }

    // Attach singleton to window
    window.OfflineSync = new OfflineSyncManager();

})(window);
