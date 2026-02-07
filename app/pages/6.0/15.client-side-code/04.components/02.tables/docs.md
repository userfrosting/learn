---
title: Data Tables with Vue 3
description: Build sortable, filterable, paginated tables using Vue 3 and modern table libraries
wip: true
---

Data tables are essential for displaying collections of information. UserFrosting 6.0 uses Vue 3 to create interactive tables with sorting, filtering, and pagination.

> [!NOTE]
> [TODO: Screenshot] - Modern data table with sorting/filtering

## Simple Table Component

Start with a basic sortable table:

```vue
<template>
  <div>
    <!-- Search/Filter -->
    <input
      v-model="searchQuery"
      type="text"
      placeholder="Search users..."
      class="uk-input uk-width-1-1"
    />

    <!-- Table -->
    <table class="uk-table uk-table-striped uk-table-hover">
      <thead>
        <tr>
          <th @click="sort('name')">
            Name
            <span v-if="sortKey === 'name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
          </th>
          <th @click="sort('email')">
            Email
            <span v-if="sortKey === 'email'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
          </th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in filteredUsers" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <button @click="editUser(user)" class="uk-button uk-button-small">Edit</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="uk-flex uk-flex-between">
      <button
        @click="previousPage"
        :disabled="currentPage === 1"
        class="uk-button uk-button-default"
      >
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="uk-button uk-button-default"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

interface User {
  id: number
  name: string
  email: string
}

const users = ref<User[]>([])
const searchQuery = ref('')
const sortKey = ref<keyof User>('name')
const sortOrder = ref<'asc' | 'desc'>('asc')
const currentPage = ref(1)
const perPage = 10

// Fetch data
onMounted(async () => {
  const response = await axios.get('/api/users')
  users.value = response.data
})

// Filtering
const filteredUsers = computed(() => {
  let filtered = users.value

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Sort
  filtered = [...filtered].sort((a, b) => {
    const aVal = a[sortKey.value]
    const bVal = b[sortKey.value]
    const modifier = sortOrder.value === 'asc' ? 1 : -1
    return aVal < bVal ? -modifier : modifier
  })

  // Pagination
  const start = (currentPage.value - 1) * perPage
  return filtered.slice(start, start + perPage)
})

const totalPages = computed(() =>
  Math.ceil(users.value.length / perPage)
)

function sort(key: keyof User) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

function previousPage() {
  if (currentPage.value > 1) currentPage.value--
}

function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function editUser(user: User) {
  console.log('Edit:', user)
}
</script>
```

## Server-Side Tables (Sprunjes)

For large datasets, fetch data from the server with pagination/sorting:

```vue
<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

interface TableData {
  rows: any[]
  count: number
  count_filtered: number
}

const data = ref<any[]>([])
const loading = ref(false)
const totalRecords = ref(0)

// Table state
const page = ref(1)
const perPage = ref(25)
const sortField = ref('name')
const sortDirection = ref<'asc' | 'desc'>('asc')
const filters = ref<Record<string, string>>({})

// Fetch data when params change
watch([page, perPage, sortField, sortDirection, filters], fetchData, { deep: true })

onMounted(fetchData)

async function fetchData() {
  loading.value = true

  try {
    const response = await axios.get<TableData>('/api/users', {
      params: {
        page: page.value,
        size: perPage.value,
        sorts: { [sortField.value]: sortDirection.value },
        filters: filters.value
      }
    })

    data.value = response.data.rows
    totalRecords.value = response.data.count_filtered
  } finally {
    loading.value = false
  }
}
</script>
```

## Using TanStack Vue Table

For advanced features, use [TanStack Table](https://tanstack.com/table/v8):

```bash
npm install @tanstack/vue-table
```

```vue
<script setup lang="ts" generic="TData extends Record<string, any>">
import {
  useVueTable,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  flexRender,
  type ColumnDef
} from '@tanstack/vue-table'
import { ref } from 'vue'

interface User {
  id: number
  name: string
  email: string
  role: string
}

const data = ref<User[]>([
  { id: 1, name: 'Alice', email: 'alice@example.com', role: 'Admin' },
  { id: 2, name: 'Bob', email: 'bob@example.com', role: 'User' },
])

const columns: ColumnDef<User>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
  },
  {
    accessorKey: 'email',
    header: 'Email',
  },
  {
    accessorKey: 'role',
    header: 'Role',
  },
  {
    id: 'actions',
    cell: ({ row }) => {
      return h('button', {
        onClick: () => console.log('Edit', row.original)
      }, 'Edit')
    }
  }
]

const table = useVueTable({
  get data() { return data.value },
  columns,
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
  <table class="uk-table">
    <thead>
      <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
        <th v-for="header in headerGroup.headers" :key="header.id">
          <div @click="header.column.getToggleSortingHandler()?.($event)">
            <component
              :is="flexRender(header.column.columnDef.header, header.getContext())"
            />
          </div>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="row in table.getRowModel().rows" :key="row.id">
        <td v-for="cell in row.getVisibleCells()" :key="cell.id">
          <component :is="flexRender(cell.column.columnDef.cell, cell.getContext())" />
        </td>
      </tr>
    </tbody>
  </table>
</template>
```

## Reusable Table Composable

```typescript
// composables/useTable.ts
import { ref, computed, Ref } from 'vue'

export function useTable<T extends Record<string, any>>(
  fetchFn: (params: any) => Promise<{ data: T[], total: number }>
) {
  const data: Ref<T[]> = ref([])
  const loading = ref(false)
  const total = ref(0)

  const page = ref(1)
  const perPage = ref(25)
  const sortKey = ref<keyof T | ''>('')
  const sortOrder = ref<'asc' | 'desc'>('asc')
  const search = ref('')

  const totalPages = computed(() => Math.ceil(total.value / perPage.value))

  async function fetch() {
    loading.value = true
    try {
      const result = await fetchFn({
        page: page.value,
        perPage: perPage.value,
        sortKey: sortKey.value,
        sortOrder: sortOrder.value,
        search: search.value
      })
      data.value = result.data
      total.value = result.total
    } finally {
      loading.value = false
    }
  }

  function sort(key: keyof T) {
    if (sortKey.value === key) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortOrder.value = 'asc'
    }
    fetch()
  }

  return {
    data,
    loading,
    total,
    totalPages,
    page,
    perPage,
    search,
    fetch,
    sort
  }
}
```

## What's Next?

- **[Forms](/client-side-code/components/forms)**: Create and edit table data
- **[Collections](/client-side-code/components/collections)**: Manage dynamic lists
- **[Alerts](/client-side-code/components/alerts)**: Show operation feedback

## Further Reading

- [TanStack Table Documentation](https://tanstack.com/table/v8)
- [UIkit Tables](https://getuikit.com/docs/table)
- [Data Sprunjing](/database/data-sprunjing)
