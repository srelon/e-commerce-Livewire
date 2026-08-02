export interface TeamMember {
    name: string
    role: string
    initials: string
    color: string
    bio: string
}

export interface Perk {
    title: string
    desc: string
    icon: string
}

export interface FaqItem {
    question: string
    answer: string
}

export interface LayoutCategory {
    id: number
    name: string
    slug: string
    icon: string | null
    image: string | null
    count: number
}

export interface LayoutMenuItem {
    id: number
    name: string
    type: 'link' | 'route'
    route: string | null
    params: Record<string, string> | null
    children: LayoutMenuItem[]
}

export interface LayoutContact {
    key: string
    name: string
    content: string
    icon: string | null
}

export interface ContactListItem {
    label: string
    value: string
    href: string | null
    icon: string | null
}
