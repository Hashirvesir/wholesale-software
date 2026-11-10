<!-- StockPOS AI Copilot Widget & Drawer -->
<div x-data="aiCopilot()"
     @toggle-ai-copilot.window="open = !open"
     @keydown.window.alt.a="open = !open"
     class="select-none">

    <!-- Floating AI Bubble Button (Desktop & Tablet) -->
    <button type="button" @click="open = true"
        class="fixed bottom-20 xl:bottom-6 right-4 sm:right-6 z-40 p-3 sm:px-4 sm:py-3 rounded-full sm:rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-purple-500 text-white shadow-2xl shadow-indigo-500/40 hover:shadow-indigo-500/60 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2.5 border-2 border-white/20 group cursor-pointer"
        title="Open StockPOS AI Copilot (Alt + A)">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
        </span>
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span class="hidden sm:inline font-black text-xs uppercase tracking-wider">Ask StockPOS AI</span>
    </button>

    <!-- Backdrop -->
    <div x-show="open" x-cloak
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-50"></div>

    <!-- AI Slide-over Drawer -->
    <div x-show="open" x-cloak
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full sm:max-w-md md:max-w-lg bg-white dark:bg-gray-900 shadow-2xl z-50 flex flex-col border-l border-gray-200 dark:border-gray-800">

        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600 text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner">
                    <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-sm tracking-wide uppercase">StockPOS AI Copilot</h3>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-300 text-[10px] font-black uppercase tracking-wider border border-emerald-400/30">Live Data</span>
                    </div>
                    <p class="text-[11px] text-indigo-100 font-medium">Business insights & task automation</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click="clearChat()" class="p-2 text-indigo-100 hover:text-white rounded-lg hover:bg-white/10 transition" title="Clear Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <button type="button" @click="open = false" class="p-2 text-indigo-100 hover:text-white rounded-lg hover:bg-white/10 transition" title="Close Drawer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Quick Prompts Horizontal Pills -->
        <div class="px-3 py-2 bg-indigo-50/70 dark:bg-gray-800/60 border-b border-indigo-100 dark:border-gray-800 flex items-center gap-1.5 overflow-x-auto scrollbar-none shrink-0">
            <button type="button" @click="sendQuickPrompt('Aaj ki sales, collection aur kharche ka summary batao.')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-gray-600 transition whitespace-nowrap shadow-2xs">
                📊 Aaj ki Summary
            </button>
            <button type="button" @click="sendQuickPrompt('Top 5 overdue customers aur unke pending balances batao.')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-gray-600 hover:bg-rose-50 dark:hover:bg-gray-600 transition whitespace-nowrap shadow-2xs">
                ⚠️ Top Overdue
            </button>
            <button type="button" @click="sendQuickPrompt('Kon se products low stock ya alert par hain aur kitne order karne chahiye?')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-gray-600 hover:bg-amber-50 dark:hover:bg-gray-600 transition whitespace-nowrap shadow-2xs">
                📦 Low Stock Alert
            </button>
            <button type="button" @click="sendQuickPrompt('Cheques aur vendor payables ka kya hisaab hai?')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-gray-600 hover:bg-emerald-50 dark:hover:bg-gray-600 transition whitespace-nowrap shadow-2xs">
                🧾 Cheques Status
            </button>
        </div>

        <!-- Chat History Messages Scroll Area -->
        <div class="flex-1 p-4 overflow-y-auto space-y-3.5 select-text" x-ref="messagesContainer">
            <!-- Welcome Bot Message -->
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-600 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow">
                    AI
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-none p-3.5 text-xs sm:text-[13px] leading-relaxed shadow-xs max-w-[88%] border border-gray-200/60 dark:border-gray-700">
                    <p class="font-bold text-indigo-600 dark:text-indigo-400 mb-1">Salam! Main StockPOS AI Copilot hoon. 🤝</p>
                    <p>Aap mujh se dukaan ki live sales, customers ka udhaar/balance, cheques, dead-stock ya inventory ke baray mein Roman Urdu ya English mein pooch saktay hain. Neechay quick buttons bhi use kar saktay hain ya bol kar pooch saktay hain!</p>
                </div>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="(msg, mIdx) in messages" :key="mIdx">
                <div class="flex items-start gap-2.5" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <template x-if="msg.role === 'assistant'">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-600 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow">
                            AI
                        </div>
                    </template>

                    <div class="p-3.5 rounded-2xl text-xs sm:text-[13px] leading-relaxed shadow-xs max-w-[88%] border"
                        :class="msg.role === 'user'
                            ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-tr-none border-indigo-500 font-medium'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-tl-none border-gray-200/60 dark:border-gray-700'">
                        <div class="whitespace-pre-wrap" x-text="msg.content"></div>
                        <div class="mt-1 flex items-center justify-between text-[10px] opacity-70">
                            <span x-text="msg.time"></span>
                            <template x-if="msg.role === 'assistant'">
                                <button type="button" @click="copyText(msg.content)" class="hover:underline flex items-center gap-0.5 ml-3">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span>Copy</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Typing Loading Indicator -->
            <div x-show="loading" x-cloak class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 italic py-1">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                <span>StockPOS AI soch raha hai...</span>
            </div>
        </div>

        <!-- Chat Input Form -->
        <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <!-- Voice Input Mic Button -->
                <button type="button" @click="toggleVoice()"
                    class="p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-center cursor-pointer"
                    :class="listening
                        ? 'bg-red-500 text-white border-red-600 animate-pulse'
                        : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                    :title="listening ? 'Listening... click to stop' : 'Bol kar poochein (Voice Input)'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <input type="text" x-model="userInput" x-ref="chatInput"
                    placeholder="Sawaal poochein (e.g. Aaj ki collection kitni hui?)..."
                    :disabled="loading"
                    class="flex-1 text-xs sm:text-sm font-semibold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-inner py-2.5" />

                <button type="submit" :disabled="loading || !userInput.trim()"
                    class="px-4 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold text-xs shadow-md transition disabled:opacity-50 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span class="hidden sm:inline">Send</span>
                </button>
            </form>
            <div class="mt-1.5 text-center text-[10px] text-gray-400 dark:text-gray-500">
                AI powered by GPT-4o-mini & Live MySQL Business Context
            </div>
        </div>

    </div>
</div>

<script>
    function aiCopilot() {
        return {
            open: false,
            loading: false,
            userInput: '',
            messages: [],
            listening: false,
            recognition: null,

            init() {
                // Initialize Web Speech API for voice in Urdu/English
                if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                    const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                    this.recognition = new SpeechRec();
                    this.recognition.continuous = false;
                    this.recognition.interimResults = false;
                    this.recognition.lang = 'ur-PK'; // Urdu / Roman Urdu support

                    this.recognition.onresult = (event) => {
                        const transcript = event.results[0][0].transcript;
                        this.userInput = transcript;
                        this.listening = false;
                        this.sendMessage();
                    };

                    this.recognition.onerror = () => {
                        this.listening = false;
                    };

                    this.recognition.onend = () => {
                        this.listening = false;
                    };
                }

                // Restore previous chat session from localStorage
                try {
                    const saved = localStorage.getItem('stockpos_ai_chat_history');
                    if (saved) {
                        this.messages = JSON.parse(saved);
                    }
                } catch(e) {}
            },

            toggleVoice() {
                if (!this.recognition) {
                    alert('Aapka browser voice recognition support nahi karta. Chrome ya modern mobile browser use karein.');
                    return;
                }

                if (this.listening) {
                    this.recognition.stop();
                    this.listening = false;
                } else {
                    this.listening = true;
                    this.recognition.start();
                }
            },

            sendQuickPrompt(text) {
                this.userInput = text;
                this.sendMessage();
            },

            async sendMessage() {
                const text = this.userInput.trim();
                if (!text || this.loading) return;

                const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                // Push user message
                this.messages.push({
                    role: 'user',
                    content: text,
                    time: timeNow,
                });
                this.userInput = '';
                this.loading = true;
                this.scrollToBottom();

                try {
                    const response = await fetch("{{ route('ai.chat') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            prompt: text,
                            history: this.messages.slice(-6).map(m => ({ role: m.role, content: m.content })),
                        }),
                    });

                    const data = await response.json();
                    const reply = data.reply || 'Koi response nahi mila.';

                    this.messages.push({
                        role: 'assistant',
                        content: reply,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    });

                    // Save history
                    try {
                        localStorage.setItem('stockpos_ai_chat_history', JSON.stringify(this.messages.slice(-20)));
                    } catch(e) {}

                } catch (err) {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Error: Request complete nahi ho saki. Please check internet connection.',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    });
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },

            clearChat() {
                this.messages = [];
                localStorage.removeItem('stockpos_ai_chat_history');
            },

            copyText(text) {
                navigator.clipboard.writeText(text);
                alert('Copied to clipboard!');
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.messagesContainer;
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        };
    }
</script>
