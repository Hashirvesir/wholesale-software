<!-- Invenco AI Copilot Widget & Drawer -->
<div x-data="aiCopilot()"
     @toggle-ai-copilot.window="open = !open"
     @keydown.window.alt.a="open = !open"
     class="select-none">

    <!-- Floating AI Bubble Button (Desktop & Tablet) -->
    <button type="button" @click="open = true"
        class="fixed bottom-20 xl:bottom-6 right-4 sm:right-6 z-40 p-3 sm:px-4 sm:py-3 rounded-full sm:rounded-2xl bg-[#252ebf] hover:bg-[#1e2599] text-white shadow-xl shadow-[#252ebf]/30 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2.5 border border-white/20 group cursor-pointer"
        title="Open Invenco AI Copilot (Alt + A)">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
        </span>
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span class="hidden sm:inline font-black text-xs uppercase tracking-wider">Ask Invenco AI</span>
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
        <div class="p-4 bg-[#252ebf] text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-black/20 flex items-center justify-center border border-white/20 shadow-inner font-black text-sm tracking-wider">
                    IN
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-sm tracking-wide uppercase">Invenco AI Copilot</h3>
                        <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-wider border border-white/30">Live Database</span>
                    </div>
                    <p class="text-[11px] text-blue-100 font-medium">Profit & Loss, Expenses, Ledgers & Insights</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click="clearChat()" class="p-2 text-blue-100 hover:text-white rounded-lg hover:bg-black/20 transition" title="Clear Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <button type="button" @click="open = false" class="p-2 text-blue-100 hover:text-white rounded-lg hover:bg-black/20 transition" title="Close Drawer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Quick Prompts Horizontal Pills -->
        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800 flex items-center gap-1.5 overflow-x-auto scrollbar-none shrink-0">
            <button type="button" @click="sendQuickPrompt('Is saal 2026 ka Profit & Loss aur net munafa batao.')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:border-[#252ebf] hover:text-[#252ebf] transition whitespace-nowrap shadow-2xs">
                📈 Is Saal Ka P&L
            </button>
            <button type="button" @click="sendQuickPrompt('Dukaan ke total kharchay (expenses) kitnay hain aur sab se baray kharchay kon se hain?')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:border-[#252ebf] hover:text-[#252ebf] transition whitespace-nowrap shadow-2xs">
                💸 Kharchay (Expenses)
            </button>
            <button type="button" @click="sendQuickPrompt('Top 5 overdue customers aur unke pending balances batao.')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:border-[#252ebf] hover:text-[#252ebf] transition whitespace-nowrap shadow-2xs">
                ⚠️ Top Overdue
            </button>
            <button type="button" @click="sendQuickPrompt('Cash aur Bank accounts ke balances kitnay hain?')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:border-[#252ebf] hover:text-[#252ebf] transition whitespace-nowrap shadow-2xs">
                🏦 Cash & Bank
            </button>
            <button type="button" @click="sendQuickPrompt('Kon se products low stock ya alert par hain?')"
                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:border-[#252ebf] hover:text-[#252ebf] transition whitespace-nowrap shadow-2xs">
                📦 Low Stock Alert
            </button>
        </div>

        <!-- Chat History Messages Scroll Area -->
        <div class="flex-1 p-4 overflow-y-auto space-y-3.5 select-text" x-ref="messagesContainer">
            <!-- Welcome Bot Message -->
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#252ebf] text-white flex items-center justify-center shrink-0 text-xs font-black tracking-wider shadow">
                    IN
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-none p-3.5 text-xs sm:text-[13px] leading-relaxed shadow-xs max-w-[88%] border border-gray-200/60 dark:border-gray-700">
                    <p class="font-bold text-[#252ebf] dark:text-blue-400 mb-1">Salam! Main Invenco AI Copilot hoon. 🤝</p>
                    <p>Mujhe Invenco ke poore database ka pata hai: <strong>is saal ka Profit & Loss</strong>, <strong>kharchon (rent, salary, bills) ka hisaab</strong>, <strong>customer udhaar/balances</strong>, <strong>cash & bank accounts</strong> aur <strong>inventory</strong>. Aap Roman Urdu ya English mein pooch saktay hain ya bol kar sawal kar saktay hain!</p>
                </div>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="(msg, mIdx) in messages" :key="mIdx">
                <div class="flex items-start gap-2.5" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <template x-if="msg.role === 'assistant'">
                        <div class="w-8 h-8 rounded-full bg-[#252ebf] text-white flex items-center justify-center shrink-0 text-xs font-black tracking-wider shadow">
                            IN
                        </div>
                    </template>

                    <div class="p-3.5 rounded-2xl text-xs sm:text-[13px] leading-relaxed shadow-xs max-w-[88%] border"
                        :class="msg.role === 'user'
                            ? 'bg-[#252ebf] text-white rounded-tr-none border-[#252ebf] font-medium'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-tl-none border-gray-200/60 dark:border-gray-700'">
                        <template x-if="msg.role === 'user'">
                            <div class="whitespace-pre-wrap" x-text="msg.content"></div>
                        </template>
                        <template x-if="msg.role === 'assistant'">
                            <div class="ai-rendered-content select-text leading-relaxed" x-html="renderMessage(msg.content)"></div>
                        </template>
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
                <span>Invenco AI database analyze kar raha hai...</span>
            </div>
        </div>

        <!-- Voice Listening Banner -->
        <div x-show="listening" x-cloak class="px-4 py-2 bg-red-500 text-white flex items-center justify-between text-xs font-bold animate-pulse">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                <span x-text="voiceStatus || '🔴 Microphone Active: Boliye (Say your question)...'"></span>
            </div>
            <button type="button" @click="stopVoice()" class="px-2 py-0.5 bg-white text-red-600 rounded text-[11px] font-black uppercase">Stop ⏹</button>
        </div>

        <!-- Chat Input Form -->
        <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <!-- Voice Input Mic Button -->
                <button type="button" @click="toggleVoice()"
                    class="p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-center cursor-pointer shrink-0"
                    :class="listening
                        ? 'bg-red-500 text-white border-red-600 ring-2 ring-red-400'
                        : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                    :title="listening ? 'Stop Recording' : 'Bol kar poochein (Click to Speak)'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <!-- Voice Language Selector Toggle -->
                <button type="button" @click="toggleVoiceLang()"
                    class="px-2 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 shrink-0"
                    :title="'Voice language: ' + (voiceLang === 'ur-PK' ? 'Urdu (اردو)' : 'English / Roman')">
                    <span x-text="voiceLang === 'ur-PK' ? 'اردو' : 'EN/ROM'"></span>
                </button>

                <input type="text" x-model="userInput" x-ref="chatInput"
                    placeholder="Sawal poochein (e.g. Is saal ka profit kitna hai?)..."
                    :disabled="loading"
                    class="flex-1 text-xs sm:text-sm font-semibold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-[#252ebf] focus:ring-[#252ebf] shadow-inner py-2.5" />

                <button type="submit" :disabled="loading || !userInput.trim()"
                    class="px-4 py-2.5 bg-[#252ebf] hover:bg-[#1e2599] text-white rounded-xl font-bold text-xs shadow-md transition disabled:opacity-50 active:scale-95 flex items-center gap-1.5 cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span class="hidden sm:inline">Send</span>
                </button>
            </form>
            <div class="mt-1.5 flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500 px-1">
                <span>AI powered by GPT-4o-mini & Invenco Live Database</span>
                <span x-show="!isSecure" class="text-amber-500 font-bold">⚠️ Mic requires HTTPS or localhost</span>
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
            voiceStatus: '',
            voiceLang: 'en-US', // en-US works reliably for English & Roman Urdu phonetics without missing language packs
            isSecure: window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1',

            init() {
                // Restore chat session from localStorage
                try {
                    const saved = localStorage.getItem('invenco_ai_chat_history');
                    if (saved) {
                        this.messages = JSON.parse(saved);
                    }
                } catch(e) {}
            },

            toggleVoiceLang() {
                this.voiceLang = this.voiceLang === 'en-US' ? 'ur-PK' : 'en-US';
            },

            toggleVoice() {
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRec) {
                    alert('Aapka browser Speech Recognition support nahi karta. Google Chrome ya Microsoft Edge use karein.');
                    return;
                }

                if (!this.isSecure) {
                    alert('Browser security restriction: Microphone/Voice input sirf HTTPS ya localhost par allow hota hai. HTTP par browser mic block kar deta hai.');
                    return;
                }

                if (this.listening) {
                    this.stopVoice();
                    return;
                }

                try {
                    this.recognition = new SpeechRec();
                    this.recognition.continuous = false;
                    this.recognition.interimResults = false;
                    this.recognition.lang = this.voiceLang || 'en-US';

                    this.recognition.onstart = () => {
                        this.listening = true;
                        this.voiceStatus = '🔴 Sun raha hoon... boliye (Speak into mic)';
                    };

                    this.recognition.onresult = (event) => {
                        const transcript = event.results[0][0].transcript;
                        if (transcript) {
                            this.userInput = transcript;
                            this.listening = false;
                            this.voiceStatus = '';
                            this.sendMessage();
                        }
                    };

                    this.recognition.onerror = (event) => {
                        console.error('Speech recognition error:', event.error);
                        this.listening = false;
                        this.voiceStatus = '';

                        if (event.error === 'not-allowed') {
                            alert('Microphone permission blocked hai! Browser ke URL bar mein lock icon par click kar ke microphone permission allow karein.');
                        } else if (event.error === 'network') {
                            // If ur-PK failed on network, fallback to en-US
                            if (this.voiceLang === 'ur-PK') {
                                this.voiceLang = 'en-US';
                                alert('Urdu voice pack connect nahi ho saka. Switching to English/Roman speech recognition. Dobara mic dabayein.');
                            } else {
                                alert('Voice speech recognition network error. Please check your internet connection.');
                            }
                        } else if (event.error === 'no-speech') {
                            // User did not speak, quiet timeout
                        } else {
                            alert('Voice error: ' + event.error);
                        }
                    };

                    this.recognition.onend = () => {
                        this.listening = false;
                        this.voiceStatus = '';
                    };

                    this.recognition.start();
                } catch(err) {
                    console.error('Recognition error:', err);
                    this.listening = false;
                    this.voiceStatus = '';
                    alert('Microphone start nahi ho saka: ' + err.message);
                }
            },

            stopVoice() {
                if (this.recognition) {
                    try { this.recognition.stop(); } catch(e) {}
                }
                this.listening = false;
                this.voiceStatus = '';
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
                        localStorage.setItem('invenco_ai_chat_history', JSON.stringify(this.messages.slice(-20)));
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
                localStorage.removeItem('invenco_ai_chat_history');
            },

            copyText(text) {
                navigator.clipboard.writeText(text);
                alert('Copied to clipboard!');
            },

            renderMessage(content) {
                if (!content) return '';
                if (window.renderMarkdown) {
                    return window.renderMarkdown(content);
                }
                // Fallback converter if marked is not yet available
                let escaped = String(content)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                return escaped
                    .replace(/^### (.*$)/gim, '<h4 class="font-bold text-indigo-600 dark:text-indigo-400 mt-2 mb-1">$1</h4>')
                    .replace(/^## (.*$)/gim, '<h3 class="font-bold text-gray-900 dark:text-white mt-2 mb-1">$1</h3>')
                    .replace(/^# (.*$)/gim, '<h2 class="font-black text-gray-900 dark:text-white mt-2 mb-1">$1</h2>')
                    .replace(/\*\*(.*?)\*\*/gim, '<strong class="font-bold text-gray-900 dark:text-white">$1</strong>')
                    .replace(/^\* (.*$)/gim, '<li class="ml-4 list-disc">$1</li>')
                    .replace(/^- (.*$)/gim, '<li class="ml-4 list-disc">$1</li>')
                    .replace(/\n/gim, '<br>');
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

<style>
.ai-rendered-content h1, .ai-rendered-content h2, .ai-rendered-content h3, .ai-rendered-content h4 {
    font-weight: 700;
    margin-top: 0.5rem;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}
.ai-rendered-content h1 { font-size: 0.95rem; }
.ai-rendered-content h2 { font-size: 0.9rem; }
.ai-rendered-content h3 { font-size: 0.85rem; color: #4f46e5; }
.dark .ai-rendered-content h3 { color: #818cf8; }
.ai-rendered-content h4 { font-size: 0.8rem; color: #6366f1; }
.ai-rendered-content strong { font-weight: 700; color: #111827; }
.dark .ai-rendered-content strong { color: #f9fafb; }
.ai-rendered-content ul {
    list-style-type: disc;
    padding-left: 1.15rem;
    margin-top: 0.25rem;
    margin-bottom: 0.4rem;
}
.ai-rendered-content ol {
    list-style-type: decimal;
    padding-left: 1.15rem;
    margin-top: 0.25rem;
    margin-bottom: 0.4rem;
}
.ai-rendered-content li {
    margin-bottom: 0.2rem;
}
.ai-rendered-content p {
    margin-bottom: 0.35rem;
}
.ai-rendered-content p:last-child {
    margin-bottom: 0;
}
.ai-rendered-content hr {
    border-color: rgba(156, 163, 175, 0.3);
    margin: 0.5rem 0;
}
</style>
