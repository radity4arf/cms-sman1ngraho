// [THECHNOLOGY-CRE] : TipTap Node extension — embed YouTube di RichEditor
// Responsive aspect-ratio 16:9, whitelist domain youtube.com / youtu.be
// Menggunakan window.FilamentRichEditor.tiptap global (disediakan Filament)
// Dimuat via dynamic import() oleh Filament RichEditor extension system

const { Node } = window.FilamentRichEditor.tiptap.core

/**
 * Ekstrak YouTube video ID dari berbagai format URL.
 * @param {string|null} url
 * @returns {string|null}
 */
const extractYoutubeId = (url) => {
    if (!url) return null
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/|youtube\.com\/live\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/watch\?.*[?&]v=([a-zA-Z0-9_-]{11})/,
    ]
    for (const pattern of patterns) {
        const match = url.match(pattern)
        if (match) return match[1]
    }
    return null
}

/**
 * Bangun embed URL dari video ID + timestamp opsional.
 * @param {string} videoId
 * @param {number} start
 * @returns {string}
 */
const buildEmbedUrl = (videoId, start = 0) => {
    const base = `https://www.youtube.com/embed/${videoId}`
    return start > 0 ? `${base}?start=${start}` : base
}

export default Node.create({
    name: 'youtube',

    group: 'block',

    atom: true,

    draggable: true,

    selectable: true,

    addAttributes() {
        return {
            src: {
                default: null,
                parseHTML: (element) => {
                    // Cek iframe di dalam element
                    const iframe = element.querySelector?.('iframe')
                    if (iframe) return iframe.getAttribute('src') || null
                    // Cek element itu sendiri (mungkin iframe langsung)
                    if (element.tagName === 'IFRAME') return element.getAttribute('src') || null
                    // Cek data attribute
                    return element.getAttribute('data-youtube-src') || null
                },
                renderHTML: (attributes) => {
                    if (!attributes.src) return {}
                    const videoId = extractYoutubeId(attributes.src)
                    if (!videoId) return {}
                    return { 'data-youtube-src': `https://www.youtube.com/watch?v=${videoId}` }
                },
            },
            start: {
                default: 0,
                parseHTML: (element) => {
                    const iframe = element.querySelector?.('iframe') || element
                    const src = iframe.getAttribute('src') || ''
                    const match = src.match(/[?&]start=(\d+)/)
                    return match ? parseInt(match[1], 10) : 0
                },
            },
        }
    },

    parseHTML() {
        return [
            { tag: 'div[data-youtube-video]' },
            { tag: 'iframe[src*="youtube.com"]' },
            { tag: 'iframe[src*="youtu.be"]' },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const src = HTMLAttributes['data-youtube-src'] || HTMLAttributes.src
        const videoId = extractYoutubeId(src)

        if (!videoId) {
            return ['div', {}, '[Invalid YouTube URL]']
        }

        const start = parseInt(HTMLAttributes['data-youtube-start'] || 0, 10)
        const embedUrl = buildEmbedUrl(videoId, start)

        return [
            'div',
            {
                'data-youtube-video': '',
                style: 'position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;',
            },
            [
                'iframe',
                {
                    src: embedUrl,
                    style: 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;',
                    frameborder: '0',
                    allowfullscreen: 'true',
                    allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                    referrerpolicy: 'strict-origin-when-cross-origin',
                },
            ],
        ]
    },

    addCommands() {
        return {
            setYoutubeVideo:
                (options) =>
                ({ commands }) => {
                    return commands.insertContent({
                        type: this.name,
                        attrs: {
                            src: options.src,
                            start: options.start || 0,
                        },
                    })
                },
        }
    },

    addNodeView() {
        return ({ node, HTMLAttributes }) => {
            const dom = document.createElement('div')
            dom.setAttribute('data-youtube-video', '')
            dom.style.position = 'relative'
            dom.style.paddingBottom = '56.25%'
            dom.style.height = '0'
            dom.style.overflow = 'hidden'
            dom.style.maxWidth = '100%'
            dom.style.marginTop = '0.5rem'
            dom.style.marginBottom = '0.5rem'

            const src = HTMLAttributes['data-youtube-src'] || node.attrs.src
            const videoId = extractYoutubeId(src)

            if (!videoId) {
                const p = document.createElement('p')
                p.textContent = '[Invalid YouTube URL]'
                p.style.color = 'red'
                dom.appendChild(p)
                return { dom }
            }

            const start = node.attrs.start || 0
            const embedUrl = buildEmbedUrl(videoId, start)

            const iframe = document.createElement('iframe')
            iframe.src = embedUrl
            iframe.style.position = 'absolute'
            iframe.style.top = '0'
            iframe.style.left = '0'
            iframe.style.width = '100%'
            iframe.style.height = '100%'
            iframe.setAttribute('frameborder', '0')
            iframe.setAttribute('allowfullscreen', 'true')
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture')
            iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin')

            dom.appendChild(iframe)

            return { dom }
        }
    },
})
