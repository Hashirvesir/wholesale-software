<!-- Invenco AI Copilot Widget & Drawer -->
<div x-data="aiCopilot()"
     @toggle-ai-copilot.window="open = !open"
     @keydown.window.alt.a="open = !open"
     class="select-none">

    <!-- Floating AI Button (Desktop & Tablet) -->
    <button type="button" @click="open = true"
        class="@if(request()->routeIs(['sales.create', 'sales.edit', 'purchases.create', 'purchases.edit', 'pos.*'])) hidden md:flex @else hidden sm:flex @endif fixed bottom-6 right-6 z-40 h-11 px-4 rounded-full bg-[#252ebf] hover:bg-[#1e2599] text-white shadow-lg shadow-[#252ebf]/25 hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-150 items-center gap-2 cursor-pointer border border-white/10"
        title="Invenco AI (Alt + A)">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <span class="text-xs font-bold tracking-wide">AI Assistant</span>
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
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs z-50"></div>

    <!-- AI Slide-over Drawer -->
    <div x-show="open" x-cloak
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white dark:bg-gray-900 shadow-2xl z-50 flex flex-col border-l border-gray-200 dark:border-gray-800">

        <!-- Header -->
        <div class="px-5 py-3.5 bg-[#252ebf] text-white flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs tracking-wider text-white">
                    IN
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">Invenco AI</h3>
                    <p class="text-[11px] text-blue-100/80">Business & Accounts Assistant</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click="clearChat()" class="p-1.5 text-blue-100 hover:text-white rounded-lg hover:bg-white/10 transition" title="Clear Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <button type="button" @click="open = false" class="p-1.5 text-blue-100 hover:text-white rounded-lg hover:bg-white/10 transition" title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Chat History Messages Scroll Area -->
        <div class="flex-1 p-4 overflow-y-auto space-y-3.5 select-text" x-ref="messagesContainer">
            <!-- Empty State / Greeting -->
            <template x-if="messages.length === 0">
                <div class="space-y-4 py-2">
                    <div class="flex items-start gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-[#252ebf] text-white flex items-center justify-center shrink-0 text-[10px] font-bold tracking-wider">
                            IN
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 text-xs sm:text-sm leading-relaxed border border-gray-200/50 dark:border-gray-700">
                            <p class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Salam! Main Invenco AI hoon.</p>
                            <p class="text-gray-600 dark:text-gray-300">Aap profit & loss, kharchay, customer balances, cash/bank ya inventory ke baray mein sawal pooch saktay hain.</p>
                        </div>
                    </div>

                    <!-- Clean Suggestions -->
                    <div class="pl-9 space-y-1.5 pt-1">
                        <button type="button" @click="sendQuickPrompt('Is saal ka Profit & Loss aur net munafa batao.')"
                            class="text-left w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 text-xs transition border border-gray-200/70 dark:border-gray-700 cursor-pointer">
                            Is saal ka Profit & Loss kitna hai?
                        </button>
                        <button type="button" @click="sendQuickPrompt('Top 5 overdue customers aur unke pending balances batao.')"
                            class="text-left w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 text-xs transition border border-gray-200/70 dark:border-gray-700 cursor-pointer">
                            Top overdue customers aur unke balances
                        </button>
                        <button type="button" @click="sendQuickPrompt('Cash aur Bank accounts ke balances kitnay hain?')"
                            class="text-left w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 text-xs transition border border-gray-200/70 dark:border-gray-700 cursor-pointer">
                            Cash aur Bank accounts ke balances
                        </button>
                    </div>
                </div>
            </template>

            <!-- Dynamic Messages -->
            <template x-for="(msg, mIdx) in messages" :key="mIdx">
                <div class="flex items-start gap-2.5" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <template x-if="msg.role === 'assistant'">
                        <div class="w-7 h-7 rounded-full bg-[#252ebf] text-white flex items-center justify-center shrink-0 text-[10px] font-bold tracking-wider">
                            IN
                        </div>
                    </template>

                    <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed max-w-[85%]"
                        :class="msg.role === 'user'
                            ? 'bg-[#252ebf] text-white rounded-tr-sm'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-tl-sm border border-gray-200/50 dark:border-gray-700'">
                        <template x-if="msg.role === 'user'">
                            <div class="whitespace-pre-wrap" x-text="msg.content"></div>
                        </template>
                        <template x-if="msg.role === 'assistant'">
                            <div class="ai-rendered-content select-text leading-relaxed" x-html="renderMessage(msg.content)"></div>
                        </template>
                        <div class="mt-1 flex items-center justify-between text-[10px] opacity-60">
                            <span x-text="msg.time"></span>
                            <template x-if="msg.role === 'assistant'">
                                <button type="button" @click="copyText(msg.content)" class="hover:underline flex items-center gap-0.5 ml-2 cursor-pointer">
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
                <span class="w-2 h-2 rounded-full bg-[#252ebf] animate-ping"></span>
                <span>Invenco AI soch raha hai...</span>
            </div>
        </div>

        <!-- Voice Listening Banner -->
        <div x-show="listening" x-cloak class="px-4 py-2 bg-red-500 text-white flex items-center justify-between text-xs font-bold animate-pulse">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                <span x-text="voiceStatus || 'Microphone Active: Boliye...'"></span>
            </div>
            <button type="button" @click="stopVoice()" class="px-2 py-0.5 bg-white text-red-600 rounded text-[11px] font-black uppercase">Stop</button>
        </div>

        <!-- Chat Input Form -->
        <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <!-- Voice Input Mic Button -->
                <button type="button" @click="toggleVoice()"
                    class="p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-center cursor-pointer shrink-0"
                    :class="listening
                        ? 'bg-red-500 text-white border-red-600 ring-2 ring-red-400'
                        : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700'"
                    :title="listening ? 'Stop Recording' : 'Voice Input'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <input type="text" x-model="userInput" x-ref="chatInput"
                    placeholder="Sawal likhein..."
                    :disabled="loading"
                    class="flex-1 text-xs sm:text-sm rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-[#252ebf] focus:ring-[#252ebf] shadow-xs py-2 px-3.5" />

                <button type="submit" :disabled="loading || !userInput.trim()"
                    class="p-2.5 bg-[#252ebf] hover:bg-[#1e2599] text-white rounded-xl font-bold shadow-sm transition disabled:opacity-30 active:scale-95 flex items-center justify-center cursor-pointer shrink-0"
                    title="Send">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
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
