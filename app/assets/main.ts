/** Setup Theme */
import './theme.less'

/**
 * Import FontAwesome
 * TODO : Use only the icons we need (via kit) instead of the full CSS
 */
import '@fortawesome/fontawesome-free/css/all.css'

/**
 * Import UIkit and its icons.
 */
import UIkit from 'uikit'
import Icons from 'uikit/dist/js/uikit-icons'
UIkit.use(Icons)
