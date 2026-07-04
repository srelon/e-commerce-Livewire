export interface PageSeo {
    title: string | null
    description: string | null
    keywords: string | null
}

export function useSeo(seo?: PageSeo | null) {
    if (!seo) return

    if (seo.title) {
        document.title = seo.title
    }

    set_meta('description', seo.description)
    set_meta('keywords', seo.keywords)
}

function set_meta(name: string, content: string | null) {
    if (!content) return

    let tag = document.querySelector(`meta[name="${name}"]`)
    if (!tag) {
        tag = document.createElement('meta')
        tag.setAttribute('name', name)
        document.head.appendChild(tag)
    }
    tag.setAttribute('content', content)
}
