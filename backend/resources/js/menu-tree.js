export default function menuTree(seed) {
    return {
        tree: JSON.parse(JSON.stringify(seed)),
        original: JSON.parse(JSON.stringify(seed)),
        is_dirty: false,

        findNode(id, nodes = this.tree) {
            for (const node of nodes) {
                if (node.id === id) return node

                const found = this.findNode(id, node.children)
                if (found) return found
            }

            return null
        },

        removeNode(id, nodes = this.tree) {
            const index = nodes.findIndex((node) => node.id === id)

            if (index !== -1) {
                return nodes.splice(index, 1)[0]
            }

            for (const node of nodes) {
                const removed = this.removeNode(id, node.children)
                if (removed) return removed
            }

            return null
        },

        onDrop(itemId, position, targetParentId) {
            const item = this.removeNode(itemId)

            if (item) {
                if (targetParentId === -1) {
                    this.tree.splice(position, 0, item)
                } else {
                    const parent = this.findNode(targetParentId)
                    const bucket = parent ? parent.children : this.tree
                    bucket.splice(position, 0, item)
                }
            }

            this.is_dirty = true
            this.resumeObserving()
        },

        pauseObserving() {
            window.Alpine.stopObservingMutations()
        },

        // SortableJS moves real DOM nodes directly while dragging (ghost/fallback
        // previews, cross-list moves) — Alpine's own MutationObserver would try to
        // re-init those raw moves and crash on stale node/child scope references.
        // pauseObserving() (wired to onChoose below) keeps it disconnected for the
        // whole gesture; this only reconnects it on the next macrotask, which always
        // runs after both Alpine's own microtask-queued reactive x-for repaint (from
        // the `tree` mutation in onDrop) and SortableJS's own onEnd/onUnchoose have
        // settled, whichever of those ends up calling this last.
        resumeObserving() {
            setTimeout(() => window.Alpine.startObservingMutations(), 0)
        },

        checkMove(evt) {
            if (evt.to.dataset.dropZone === 'children' && evt.dragged.dataset.hasChildren === '1') {
                return false
            }

            return true
        },

        cancelOrder() {
            this.tree = JSON.parse(JSON.stringify(this.original))
            this.is_dirty = false
        },

        commitOrder() {
            this.$wire.saveOrder(this.tree).then(() => {
                this.original = JSON.parse(JSON.stringify(this.tree))
                this.is_dirty = false
            })
        },
    }
}
