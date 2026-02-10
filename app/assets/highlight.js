import CopyButtonPlugin from 'highlightjs-copy'
import hljs from 'highlight.js'
import hljsDefineVue from 'highlightjs-vue'

// Initialize Highlight.js with Copy Button plugin
hljs.addPlugin(
    new CopyButtonPlugin({
        autohide: false // Always show the copy button
    })
)
hljsDefineVue(hljs) // Register Vue language definition
hljs.highlightAll()
