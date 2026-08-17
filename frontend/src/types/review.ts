export interface Review {
    id: number
    parent_id: number | null
    replied_to_comment_id: number | null
    replied_to: {
        id: number
        user: {
            name: string | null
        }
    } | null
    user: {
        name: string | null
        avatar: string | null
        is_moderator: boolean
    }
    rating: number | null
    body: string
    created_at: string
    can_edit: boolean
    can_delete: boolean
    likes_count: number
    dislikes_count: number
    my_reaction: 'like' | 'dislike' | null
    replies_count: number
    replies: Review[]
}

export type ViewerReview = Review

export type RatingBreakdown = Record<1 | 2 | 3 | 4 | 5, number>
