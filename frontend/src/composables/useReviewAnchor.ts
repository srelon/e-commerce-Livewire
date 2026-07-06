const FLASH_CLASS = 'review-item--flash'
const FLASH_DURATION_MS = 1200

export function useReviewAnchor() {
    function scroll_to_review(id: number) {
        const el = document.getElementById(`review-${id}`)
        if (!el) return

        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        el.classList.add(FLASH_CLASS)
        setTimeout(() => el.classList.remove(FLASH_CLASS), FLASH_DURATION_MS)
    }

    function open_replies_and_scroll(parent_id: number, scroll_to_id: number) {
        window.dispatchEvent(new CustomEvent('review:open-replies', {
            detail: { parent_id, scroll_to: scroll_to_id },
        }))
    }

    return { scroll_to_review, open_replies_and_scroll }
}
