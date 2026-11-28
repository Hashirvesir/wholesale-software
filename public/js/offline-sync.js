/**
 * Offline Sync Manager for Wholesaler Software
 * Handles offline caching, queues, connectivity detection, and auto-sync.
 */
(function (window) {
    'use strict';

    const STORAGE_KEY_SALES = 'invenco_offline_sales_queue';
    const STORAGE_KEY_PAYMENTS = 'invenco_offline_payments_queue';
    const STORAGE_KEY_CUSTOMERS = 'invenco_offline_customers_queue';
    const STORAGE_KEY_MASTER = 'invenco_offline_master_data';
    const STORAGE_KEY_LAST_SYNC = 'invenco_offline_last_sync';

    class OfflineSyncManager {
        constructor() {
            this.isOnline = navigator.onLine;
            this.isSyncing = false;
            this.lastSyncTime = localStorage.getItem(STORAGE_KEY_LAST_SYNC) || null;
            this.salesQueue = this.loadQueue(STORAGE_KEY_SALES);
            this.paymentsQueue = this.loadQueue(STORAGE_KEY_PAYMENTS);
            this.customersQueue = this.loadQueue(STORAGE_KEY_CUSTOMERS);
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
                // Instantly update online flag so UI responds without delay
                this.isOnline = true;
                this.dispatchStatusEvent();
                // Verify with server ping and trigger sync
                this.checkConnection(true);
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.dispatchStatusEvent();
            });

            // Focus & Visibility listeners: auto-reconnect when user returns to window/tab
            window.addEventListener('focus', () => {
                if (navigator.onLine) {
                    this.checkConnection(true);
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && navigator.onLine) {
                    this.checkConnection(true);
                }
            });

            // 3. Periodic real ping check (every 45s)
            this.checkConnection(false);
            this.pingTimer = setInterval(() => {
                this.checkConnection(false);
            }, 45000);

            // 4. Initial Master Data sync ONLY if online and master data is not yet cached or stale (> 20 mins)
            if (this.isOnline && this.shouldFetchMasterData()) {
                setTimeout(() => this.fetchMasterData(), 4000);
            }
        }

        async checkConnection(triggerSyncIfOnline = false) {
            // If browser itself reports offline, definitely offline
            if (!navigator.onLine) {
                if (this.isOnline) {
                    this.isOnline = false;
                    this.dispatchStatusEvent();
                }
                return false;
            }

            const wasOffline = !this.isOnline;
            let reachedServer = false;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 6000);

                const res = await fetch('/api/offline/ping?_t=' + Date.now(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                // Any HTTP response (200-499) means connection to the server is active
                reachedServer = res.ok || (res.status >= 200 && res.status < 500);
            } catch (e) {
                // If primary ping fails (e.g. timeout on slow network reconnect),
                // attempt lightweight fallback ping to /up healthcheck
                try {
                    const fallbackController = new AbortController();
                    const fbTimeout = setTimeout(() => fallbackController.abort(), 4000);
                    const fbRes = await fetch('/up?_t=' + Date.now(), {
                        method: 'GET',
                        cache: 'no-store',
                        signal: fallbackController.signal
                    });
                    clearTimeout(fbTimeout);
                    reachedServer = fbRes.ok || (fbRes.status >= 200 && fbRes.status < 500);
                } catch (fbErr) {
                    reachedServer = false;
                }
            }

            this.isOnline = reachedServer;
            this.dispatchStatusEvent();

            const hasRecentError = this.lastErrorTimestamp && (Date.now() - this.lastErrorTimestamp < 60000);
            if (this.isOnline && (wasOffline || triggerSyncIfOnline || (this.getPendingCount() > 0 && !hasRecentError))) {
                this.syncAll(triggerSyncIfOnline);
            }

            return this.isOnline;
        }

        getPendingCount() {
            return this.salesQueue.length + this.paymentsQueue.length + this.customersQueue.length;
        }

        dispatchStatusEvent() {
            const detail = {
                isOnline: this.isOnline,
                isSyncing: this.isSyncing,
                pendingCount: this.getPendingCount(),
                salesCount: this.salesQueue.length,
                paymentsCount: this.paymentsQueue.length,
                customersCount: this.customersQueue.length,
                lastSyncTime: this.lastSyncTime
            };

            window.dispatchEvent(new CustomEvent('offline-status-changed', { detail }));
        }

        shouldFetchMasterData() {
            if (!this.masterData) return true;
            const ts = localStorage.getItem('invenco_offline_last_sync_timestamp');
            if (!ts) return true;
            return (Date.now() - parseInt(ts, 10)) > (20 * 60 * 1000);
        }

        async fetchMasterData(force = false) {
            if (!this.isOnline) return false;
            if (!force && !this.shouldFetchMasterData()) return false;
            if (this.isFetchingMasterData) return false;
            this.isFetchingMasterData = true;

            try {
                const res = await fetch('/api/offline/master-data', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (res.ok) {
                    const data = await res.json();
                    this.masterData = data;
                    localStorage.setItem(STORAGE_KEY_MASTER, JSON.stringify(data));
                    localStorage.setItem('invenco_offline_last_sync_timestamp', Date.now().toString());
                    this.lastSyncTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    localStorage.setItem(STORAGE_KEY_LAST_SYNC, this.lastSyncTime);
                    this.dispatchStatusEvent();
                    return true;
                }
            } catch (e) {
                console.warn('Failed to fetch master data:', e);
            } finally {
                this.isFetchingMasterData = false;
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
         * Enqueue an offline customer.
         */
        saveOfflineCustomer(customerData) {
            const offlineId = 'OFFLINE-CUST-' + Date.now() + '-' + Math.random().toString(36).substring(2, 7);
            const openingBal = parseFloat(customerData.opening_balance) || 0;
            const balType = customerData.balance_type || 'debit';
            const enriched = {
                offline_id: offlineId,
                id: offlineId,
                name: customerData.name,
                phone: customerData.phone || '',
                address: customerData.address || '',
                price_tier: customerData.price_tier || 'retail',
                opening_balance: openingBal,
                balance_type: balType,
                current_balance: (balType === 'credit' ? -1 : 1) * openingBal,
                is_offline: true,
                client_created_at: new Date().toISOString()
            };

            this.customersQueue.push(enriched);
            this.saveQueue(STORAGE_KEY_CUSTOMERS, this.customersQueue);

            // Also dynamically append to cached masterData.customers if available
            if (this.masterData && Array.isArray(this.masterData.customers)) {
                this.masterData.customers.unshift(enriched);
                try {
                    localStorage.setItem(STORAGE_KEY_MASTER, JSON.stringify(this.masterData));
                } catch(e){}
            }

            this.showSyncToast(`Customer "${enriched.name}" offline save ho gaya! Online aane par sync ho jayega.`);

            if (this.isOnline) {
                this.syncAll();
            }

            return enriched;
        }

        /**
         * Delete / remove a pending sale from offline queue.
         */
        deleteOfflineSale(offlineId) {
            const initialLen = this.salesQueue.length;
            this.salesQueue = this.salesQueue.filter(s => s.offline_id !== offlineId && s.id !== offlineId);
            if (this.salesQueue.length !== initialLen) {
                this.saveQueue(STORAGE_KEY_SALES, this.salesQueue);
                this.dispatchStatusEvent();
                this.showSyncToast('Offline sale queue se delete kar di gayi.');
                return true;
            }
            return false;
        }

        /**
         * Delete / remove a pending payment from offline queue.
         */
        deleteOfflinePayment(offlineId) {
            const initialLen = this.paymentsQueue.length;
            this.paymentsQueue = this.paymentsQueue.filter(p => p.offline_id !== offlineId && p.id !== offlineId);
            if (this.paymentsQueue.length !== initialLen) {
                this.saveQueue(STORAGE_KEY_PAYMENTS, this.paymentsQueue);
                this.dispatchStatusEvent();
                this.showSyncToast('Offline payment queue se delete kar di gayi.');
                return true;
            }
            return false;
        }

        /**
         * Synchronize pending items to server.
         */
        async syncAll(force = false) {
            if (this.isSyncing) return;
            if (this.getPendingCount() === 0) return;
            if (!this.isOnline) return;

            if (force) {
                this.lastErrorTimestamp = null;
            }

            this.isSyncing = true;
            this.dispatchStatusEvent();

            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenEl ? tokenEl.getAttribute('content') : '';

            const payload = {
                customers: this.customersQueue,
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
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const result = await res.json();

                    // Remove successfully synced customers
                    if (result.synced_customers && result.synced_customers.length > 0) {
                        const syncedCustIds = new Set(result.synced_customers.map(c => c.offline_id));
                        this.customersQueue = this.customersQueue.filter(c => !syncedCustIds.has(c.offline_id));
                        this.saveQueue(STORAGE_KEY_CUSTOMERS, this.customersQueue);

                        // If any offline sales were tied to an offline customer ID, update party_id to real server_id
                        result.synced_customers.forEach(sc => {
                            let updated = false;
                            this.salesQueue.forEach(s => {
                                if (s.party_id === sc.offline_id) {
                                    s.party_id = sc.server_id;
                                    updated = true;
                                }
                            });
                            if (updated) {
                                this.saveQueue(STORAGE_KEY_SALES, this.salesQueue);
                            }
                        });
                    }

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
                    const totalSynced = (result.synced_customers ? result.synced_customers.length : 0) +
                                        (result.synced_sales ? result.synced_sales.length : 0) +
                                        (result.synced_payments ? result.synced_payments.length : 0);

                    if (totalSynced > 0) {
                        this.showSyncToast(`Synced ${totalSynced} offline transaction(s) to server successfully!`);
                        // Refresh cached master data
                        this.fetchMasterData();
                    }

                    // Check if server reported errors on any items
                    if (result.errors && result.errors.length > 0) {
                        this.lastErrorTimestamp = Date.now();
                        const firstErr = result.errors[0];
                        this.showSyncErrorToast(`Sync Issue: ${firstErr.message || 'Some items failed to sync'}`);
                    } else {
                        this.lastErrorTimestamp = null;
                    }

                    window.dispatchEvent(new CustomEvent('offline-sync-completed', { detail: result }));
                } else {
                    console.error('Offline sync failed HTTP status:', res.status);
                    this.lastErrorTimestamp = Date.now();
                    if (res.status === 401) {
                        this.showSyncErrorToast('Session expired. Please log in again to sync offline data.');
                    }
                }
            } catch (err) {
                console.error('Offline sync network error:', err);
                this.lastErrorTimestamp = Date.now();
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

        showSyncErrorToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 right-5 z-[9999] bg-rose-600 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs font-black flex items-center gap-3';
            toast.innerHTML = `
                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 6000);
        }
    }

    // Attach singleton to window
    window.OfflineSync = new OfflineSyncManager();

})(window);
