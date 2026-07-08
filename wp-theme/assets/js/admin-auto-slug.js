document.addEventListener('DOMContentLoaded', () => {
    // These IDs are standard on the WP taxonomy edit/add screen
    const nameInput = document.getElementById('tag-name');
    const slugInput = document.getElementById('tag-slug');

    if (!nameInput || !slugInput) return;

    const transliterate = (text) => {
        const cyrillic = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
            'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'i', 'к': 'k', 'л': 'l', 'м': 'm',
            'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
            'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'shch',
            'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
            ' ': '-', '_': '-', ',': '', '.': '', '?': '', '!': '', '@': '', '#': '',
            '$': '', '%': '', '^': '', '&': '', '*': '', '(': '', ')': '', '+': '',
            '=': '', '{': '', '}': '', '[': '', ']': '', '|': '', '\\': '', ':': '',
            ';': '', '"': '', "'": '', '<': '', '>': '', '/': '', '`': '', '~': '',
            '№': 'no'
        };
        
        return text.toLowerCase().split('').map(char => {
            return cyrillic[char] !== undefined ? cyrillic[char] : char;
        }).join('').replace(/[^a-z0-9\-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '');
    };
    
    // We only auto-fill if the user is typing in the name field
    nameInput.addEventListener('input', () => {
        // Also fire a change event so WP knows the field was updated
        slugInput.value = transliterate(nameInput.value);
        slugInput.dispatchEvent(new Event('change', { bubbles: true }));
    });
});
