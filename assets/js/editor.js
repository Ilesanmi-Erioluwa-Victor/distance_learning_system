/* WBDLS — editor.js (basic content helpers) */
(function () {
    'use strict';

    // Wrap a textarea in a simple editor toolbar
    document.querySelectorAll('.rich-text').forEach(textarea => {
        const wrapper = document.createElement('div');
        wrapper.className = 'rt-wrapper';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(textarea);

        const tools = document.createElement('div');
        tools.className = 'rt-tools';
        tools.innerHTML = '<button type="button" data-cmd="bold"><b>B</b></button>' +
            '<button type="button" data-cmd="italic"><i>I</i></button>' +
            '<button type="button" data-cmd="underline"><u>U</u></button>' +
            '<button type="button" data-cmd="h3">H3</button>' +
            '<button type="button" data-cmd="p">P</button>' +
            '<button type="button" data-cmd="ul">• List</button>' +
            '<button type="button" data-cmd="ol">1. List</button>' +
            '<button type="button" data-cmd="link">Link</button>';
        wrapper.insertBefore(tools, textarea);

        tools.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function () {
                const cmd = this.dataset.cmd;
                textarea.focus();
                if (cmd === 'link') {
                    const url = prompt('Enter URL:');
                    if (url) document.execCommand('createLink', false, url);
                } else if (cmd === 'h3') {
                    document.execCommand('formatBlock', false, 'h3');
                } else if (cmd === 'p') {
                    document.execCommand('formatBlock', false, 'p');
                } else if (cmd === 'ul') {
                    document.execCommand('insertUnorderedList');
                } else if (cmd === 'ol') {
                    document.execCommand('insertOrderedList');
                } else {
                    document.execCommand(cmd);
                }
            });
        });
    });
})();
