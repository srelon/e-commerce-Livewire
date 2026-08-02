import type { PageSeo } from '@/composables/useSeo'
import type { RouteLocationRaw } from 'vue-router'

export interface ProductStock {
    price: string | null
    before_price: string | null
    id?: number | null
    quantity?: number
}

export interface ProductSummary {
    slug: string
    title: string
    author: string | null
    category: string | null
    rating: number | null
    image: string | null
    stock: ProductStock
}

export interface ProductDetail {
    slug: string
    title: string
    author: string | null
    category: string | null
    rating: number | null
    reviews_count: number
    short_description: string | null
    description: string | null
    images: string[]
    stock: Required<ProductStock>
    related: ProductSummary[]
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

export interface BlogPostDetail {
    title: string
    slug: string
    excerpt: string | null
    content: string | null
    author: string | null
    date: string | null
    image: string | null
    category: string | null
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

export interface DeliveryBranchOption {
    id: number
    city: string
    branch: string
}

export interface DeliveryOption {
    id: number
    name: string
    price: string | null
    requires_branch: boolean
    branches: DeliveryBranchOption[]
}

export interface PaymentOption {
    key: string
    name: string
}

export interface SavedContact {
    first_name: string
    last_name: string
    phone: string
    email: string
    delivery_id: number | null
    branch_id: number | null
}

export interface PageBundle {
    title: string
    content: string | null
    excerpt: string | null
    image: string | null
    seo: PageSeo
}

export interface CartItem {
    id: string
    title: string
    author: string
    price: number
    image: string
    href: RouteLocationRaw
    quantity: number
    available: number
}

export interface ServerCartItem {
    slug: string
    title: string
    author: string | null
    image: string | null
    price: string | null
    quantity: number
    available: number
}

export interface LastOrder {
    public_id: string
}

export interface ContactData {
    first_name: string
    last_name: string
    phone: string
    email: string
}

export interface DeliveryData {
    delivery_id: number | null
    branch_id: number | null
}

export interface PaymentData {
    method: string
}
