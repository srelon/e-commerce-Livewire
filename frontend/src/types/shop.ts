import type { PageSeo } from '@/composables/useSeo'

export interface ProductSummary {
    slug: string
    title: string
    author: string | null
    category: string | null
    price: string | null
    rating: number
    image: string | null
}

export interface AuthorSummary {
    name: string
    slug: string
    content: string | null
    photo: string | null
}

export interface BlogPostSummary {
    title: string
    slug: string
    date: string | null
    image: string | null
    category: string | null
    excerpt?: string | null
    author?: string | null
}

export interface FilterGroupItem {
    name: string
    count?: number
}

export interface FilterGroup {
    title: string
    type: 'price' | 'checkbox' | 'rating'
    query_key: string
    items?: FilterGroupItem[]
    min?: number
    max?: number
}

export interface Pagination {
    current_page: number
    last_page: number
    total: number
}

export interface Paginated<T> {
    data: T[]
    pagination: Pagination
}

export interface AuthorListItem {
    slug: string
    name: string
    initials: string
    color: string
    bio: string
    books: number
    bestsellers: number
}

export interface PageBundle {
    title: string
    content: string | null
    excerpt: string | null
    image: string | null
    seo: PageSeo
}
