import api from '@/plugins/axios'

export interface ReviewWriteData {
    rating?: number | null
    body: string
    parent_id?: number
    replied_to_comment_id?: number | null
}

export function useReviewApi() {
    function create_review(product_slug: string, data: ReviewWriteData) {
        return api.post(`products/${product_slug}/reviews`, data)
    }

    function update_review(product_slug: string, review_id: number, data: ReviewWriteData) {
        return api.put(`products/${product_slug}/reviews/${review_id}`, data)
    }

    function delete_review(product_slug: string, review_id: number) {
        return api.delete(`products/${product_slug}/reviews/${review_id}`)
    }

    function react_to_review(product_slug: string, review_id: number, type: 'like' | 'dislike') {
        return api.post(`products/${product_slug}/reviews/${review_id}/react`, { type })
    }

    function report_review(product_slug: string, review_id: number, reason: string) {
        return api.post(`products/${product_slug}/reviews/${review_id}/report`, { reason })
    }

    return { create_review, update_review, delete_review, react_to_review, report_review }
}
