<!-- AI WhatsApp Payment Recovery Modal -->
<div x-data="aiRecoveryModal()"
     @open-ai-recovery.window="openModal($event.detail.partyId)"
     class="select-none">

    <!-- Modal Backdrop -->
    <div x-show="modalOpen" x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/75 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-6 overflow-y-auto">

        <!-- Modal Dialog -->
        <div @click.away="if (!loading) modalOpen = false"
            class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all my-8">

            <!-- Modal Header -->
            <div class="px-5 py-4 bg-[#252ebf] text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-black/20 flex items-center justify-center shadow-inner font-bold text-xs tracking-wider">
                        IN
                    </div>
                    <div>
                        <h3 class="font-black text-sm uppercase tracking-wide">AI Payment Recovery Drafter</h3>
                        <p class="text-[11px] text-blue-100">Polite & context-aware WhatsApp reminder in Roman Urdu</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-blue-100 hover:text-white text-xl font-black p-1 leading-none">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <!-- Loading State -->
                <div x-show="loading" class="py-8 text-center space-y-3">
                    <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-300">Invenco AI is crafting customized recovery message...</p>
                </div>

                <!-- Content State -->
                <div x-show="!loading && messageData" class="space-y-4">
                    <!-- Party & Balance Badge -->
                    <div class="p-3 rounded-xl bg-indigo-50 dark:bg-gray-900/60 border border-indigo-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block">Customer</span>
                            <span class="font-black text-sm text-gray-900 dark:text-gray-100" x-text="messageData?.customer_name"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black uppercase text-gray-400 block">Pending Balance</span>
                            <span class="font-black text-sm text-red-600 dark:text-red-400 font-mono" x-text="'Rs. ' + Number(messageData?.balance || 0).toLocaleString()"></span>
                        </div>
                    </div>

                    <!-- Editable Message Textarea -->
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Generated WhatsApp Message (Editable)
                        </label>
                        <textarea x-model="messageData.message" rows="5"
                            class="w-full text-xs font-semibold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-inner p-3 leading-relaxed"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between gap-2 pt-2">
                        <button type="button" @click="copyMessage()"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Copy Text</span>
                        </button>

                        <button type="button" @click="openWhatsApp()" :disabled="!messageData?.whatsapp_url"
                            class="px-5 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20ba5a] text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-1.5 active:scale-95 cursor-pointer disabled:opacity-50">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            </svg>
                            <span x-text="messageData?.whatsapp_url ? 'Send via WhatsApp' : 'No Phone Number'"></span>
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="errorMessage" class="p-3 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs font-bold rounded-xl" x-text="errorMessage"></div>
            </div>

        </div>
    </div>
</div>

<script>
    function aiRecoveryModal() {
        return {
            modalOpen: false,
            loading: false,
            messageData: null,
            errorMessage: '',

            async openModal(partyId) {
                if (!partyId) return;

                this.modalOpen = true;
                this.loading = true;
                this.messageData = null;
                this.errorMessage = '';

                try {
                    const response = await fetch("{{ route('ai.recovery-message') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ party_id: partyId }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        this.errorMessage = data.message || 'AI message generate nahi ho saka.';
                        return;
                    }

                    this.messageData = data;
                } catch (err) {
                    this.errorMessage = 'Network error: ' + err.message;
                } finally {
                    this.loading = false;
                }
            },

            copyMessage() {
                if (!this.messageData?.message) return;
                navigator.clipboard.writeText(this.messageData.message);
                alert('Recovery message copied to clipboard!');
            },

            openWhatsApp() {
                if (!this.messageData?.whatsapp_url) return;
                // Update text with any edits made by the user
                const base = this.messageData.whatsapp_url.split('?text=')[0];
                const finalUrl = base + '?text=' + encodeURIComponent(this.messageData.message);
                window.open(finalUrl, '_blank');
                this.modalOpen = false;
            }
        };
    }
</script>
