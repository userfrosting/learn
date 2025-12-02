import CopyButtonPlugin from 'highlightjs-copy'
import hljs from 'highlight.js'

// Initialize Highlight.js with Copy Button plugin
hljs.addPlugin(
    new CopyButtonPlugin({
        autohide: false // Always show the copy button
    })
)
hljs.highlightAll()
