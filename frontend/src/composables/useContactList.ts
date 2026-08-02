import { computed } from 'vue'
import { useLayoutStore } from '@/stores/layout'
import type { ContactListItem } from '@/types/global'

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
