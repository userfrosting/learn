import CopyButtonPlugin from 'highlightjs-copy'
import hljs from 'highlight.js'

// Define Vue language for Highlight.js
// Based on: https://github.com/highlightjs/highlightjs-vue
hljs.registerLanguage('vue', function (hljs) {
    return {
        subLanguage: 'xml',
        contains: [
            hljs.COMMENT('<!--', '-->', {
                relevance: 10
            }),
            {
                begin: /^(\s*)(<script>)/gm,
                end: /^(\s*)(<\/script>)/gm,
                subLanguage: 'javascript',
                excludeBegin: true,
                excludeEnd: true
            },
            {
                begin: /^(\s*)(<script lang=["']ts["']>)/gm,
                end: /^(\s*)(<\/script>)/gm,
                subLanguage: 'typescript',
                excludeBegin: true,
                excludeEnd: true
            },
            {
                begin: /^(\s*)(<style(\sscoped)?>)/gm,
                end: /^(\s*)(<\/style>)/gm,
                subLanguage: 'css',
                excludeBegin: true,
                excludeEnd: true
            },
            {
                begin: /^(\s*)(<style lang=["'](scss|sass)["'](\sscoped)?>)/gm,
                end: /^(\s*)(<\/style>)/gm,
                subLanguage: 'scss',
                excludeBegin: true,
                excludeEnd: true
            },
            {
                begin: /^(\s*)(<style lang=["']stylus["'](\sscoped)?>)/gm,
                end: /^(\s*)(<\/style>)/gm,
                subLanguage: 'stylus',
                excludeBegin: true,
                excludeEnd: true
            }
        ]
    }
})

// Initialize Highlight.js with Copy Button plugin
hljs.addPlugin(
    new CopyButtonPlugin({
        autohide: false // Always show the copy button
    })
)
hljs.highlightAll()
