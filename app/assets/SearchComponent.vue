<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'

/**
 * Configuration Constants
 */
const minLength: number = 3
const dataUrl: string = '/api/search'

/**
 * Reactive Variables
 */
const searchQuery = ref<string>('')
const loading = ref<boolean>(false)
const error = ref<any>(null)
const data = ref<ResultData>({
    count: 0,
    size: 0,
    page: 0,
    rows: []
})

/**
 * Api fetch function
 */
async function fetch() {
    if (searchQuery.value.length < minLength) {
        return
    }

    loading.value = true
    axios
        .get<ResultData>(dataUrl, {
            params: {
                q: searchQuery.value
            }
        })
        .then((response) => {
            data.value = response.data
        })
        .catch((err) => {
            error.value = err.response.data
        })
        .finally(() => {
            loading.value = false
        })
}

/**
 * Watchers
 */
watch(searchQuery, async () => {
    fetch()
})

/**
 * Computed Properties
 */
const placeholder = ref<string>(`Type at least ${minLength} characters to search`)

/**
 * Interfaces
 */
interface ResultData {
    count: number
    size: number
    page: number
    rows: Result[]
}

interface Result {
    title: string
    slug: string
    route: string
    snippet: string
    score: number
    version: string
}
</script>

<template>
    <div class="uk-margin-small uk-inline uk-width-expand">
        <span class="uk-form-icon" uk-icon="icon: search"></span>
        <input
            class="uk-input"
            type="text"
            placeholder="Search Documentation"
            aria-label="Search Documentation"
            uk-toggle="target: #search-modal" />
    </div>

    <!-- This is the modal -->
    <div id="search-modal" uk-modal>
        <div class="uk-modal-dialog">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <div class="uk-modal-header">
                <h2 class="uk-modal-title">Search Documentation</h2>
            </div>

            <div class="uk-modal-body">
                <div class="uk-margin-small uk-inline uk-width-expand">
                    <span class="uk-form-icon" uk-icon="icon: search"></span>
                    <input
                        class="uk-input"
                        v-model="searchQuery"
                        type="text"
                        :placeholder="placeholder"
                        aria-label="Search Documentation"
                        autofocus
                        tabindex="1" />
                </div>

                <div class="uk-margin" uk-overflow-auto>
                    <div v-if="loading" class="uk-text-center">
                        <div uk-spinner></div>
                    </div>
                    <div v-else-if="error" class="uk-alert-danger" uk-alert>
                        <p>{{ error }}</p>
                    </div>
                    <div
                        v-else-if="data.rows.length === 0 && searchQuery.length >= minLength"
                        class="uk-text-center uk-text-muted">
                        <p>No results found</p>
                    </div>
                    <ul v-else-if="data.rows.length > 0" class="uk-list uk-list-divider">
                        <li v-for="row in data.rows" :key="row.route">
                            <a :href="row.route" class="uk-link-reset">
                                <h4 class="uk-margin-remove">{{ row.title }}</h4>
                                <p class="uk-text-small" v-html="row.snippet"></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="uk-modal-footer uk-text-right">
                <button class="uk-button uk-button-primary uk-modal-close" type="button">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
