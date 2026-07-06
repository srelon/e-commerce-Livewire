export interface AppNotification {
    id: number
    type: 'reply' | 'like' | 'dislike' | 'system'
    data: {
        from_name?: string
        review_preview?: string
        message?: string
    }
    from_user: {
        name: string | null
        avatar: string | null
    } | null
    product: {
        slug: string
        title: string
    } | null
    review_id: number | null
    parent_id: number | null
    read_at: string | null
    created_at: string
}
