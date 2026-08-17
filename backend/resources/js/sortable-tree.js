export default function sortableTree(seed) {
    return {
        tree: JSON.parse(JSON.stringify(seed)),
        original: JSON.parse(JSON.stringify(seed)),
        is_dirty: false,
        resume_timeout: null,

        findNode(id, nodes = this.tree) {
            for (const node of nodes) {
                if (node.id === id) return node

                const found = this.findNode(id, node.children ?? [])
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
                const removed = this.removeNode(id, node.children ?? [])
                if (removed) return removed
            }

            return null
        },

        onDrop(itemId, position, targetParentId = -1) {
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
            clearTimeout(this.resume_timeout)
            window.Alpine.stopObservingMutations()
        },

        resumeObserving() {
            clearTimeout(this.resume_timeout)
            this.resume_timeout = setTimeout(() => window.Alpine.startObservingMutations(), 0)
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
