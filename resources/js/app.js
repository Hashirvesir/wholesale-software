import './bootstrap';
import { marked } from 'marked';
import Alpine from 'alpinejs';

marked.setOptions({
    gfm: true,
    breaks: true,
});

window.marked = marked;
window.renderMarkdown = function(content) {
    if (!content) return '';
    try {
        return marked.parse(String(content));
    } catch (e) {
        return String(content);
    }
};

window.Alpine = Alpine;

Alpine.start();
