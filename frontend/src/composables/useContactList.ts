import { computed } from 'vue'
import { useLayoutStore } from '@/stores/layout'

export interface ContactListItem {
    label: string
    value: string
    href: string | null
    icon: string | null
}

export function useContactList() {
    const layout_store = useLayoutStore()

    return computed<ContactListItem[]>(() => layout_store.contacts.map((contact) => ({
        label: contact.name,
        value: contact.content,
        href: contact.key === 'email'
            ? `mailto:${contact.content}`
            : contact.key === 'phone'
                ? `tel:${contact.content.replace(/[^0-9+]/g, '')}`
                : null,
        icon: contact.icon,
    })))
}
