import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import axios from 'axios'
import SearchComponent from './SearchComponent.vue'

vi.mock('axios')

describe('SearchComponent', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        // Reset location to default
        Object.defineProperty(window, 'location', {
            value: { pathname: '/' },
            writable: true
        })
    })

    it('renders the search trigger input', () => {
        const wrapper = mount(SearchComponent)
        const inputs = wrapper.findAll('input[type="text"]')
        expect(inputs.length).toBeGreaterThan(0)
    })

    it('does not show results or error on initial render', () => {
        const wrapper = mount(SearchComponent)
        expect(wrapper.find('[uk-spinner]').exists()).toBe(false)
        expect(wrapper.find('.uk-alert-danger').exists()).toBe(false)
        expect(wrapper.find('.uk-list').exists()).toBe(false)
    })

    it('does not fetch when query is shorter than minimum length', async () => {
        const axiosGet = vi.mocked(axios.get)
        const wrapper = mount(SearchComponent)

        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('ab')
        await wrapper.vm.$nextTick()

        expect(axiosGet).not.toHaveBeenCalled()
    })

    it('fetches results when query meets minimum length', async () => {
        const mockData = {
            count: 1,
            size: 10,
            page: 1,
            rows: [
                {
                    title: 'Test Page',
                    slug: 'test-page',
                    route: '/6.0/test-page',
                    snippet: 'A test snippet',
                    score: 1.0,
                    version: '6.0'
                }
            ]
        }
        vi.mocked(axios.get).mockResolvedValue({ data: mockData })

        const wrapper = mount(SearchComponent)
        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('test')

        // Flush the watcher and async call
        await new Promise((resolve) => setTimeout(resolve, 0))
        await wrapper.vm.$nextTick()

        expect(axios.get).toHaveBeenCalledWith('/api/search', { params: { q: 'test' } })
    })

    it('includes version in search params when browsing a versioned page', async () => {
        Object.defineProperty(window, 'location', {
            value: { pathname: '/5.1/quick-start' },
            writable: true
        })

        vi.mocked(axios.get).mockResolvedValue({
            data: { count: 0, size: 10, page: 1, rows: [] }
        })

        const wrapper = mount(SearchComponent)
        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('test')

        await new Promise((resolve) => setTimeout(resolve, 0))
        await wrapper.vm.$nextTick()

        expect(axios.get).toHaveBeenCalledWith('/api/search', {
            params: { q: 'test', version: '5.1' }
        })
    })

    it('shows no results message when query is long enough but no results returned', async () => {
        const mockData = { count: 0, size: 10, page: 1, rows: [] }
        vi.mocked(axios.get).mockResolvedValue({ data: mockData })

        const wrapper = mount(SearchComponent)
        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('xyz')

        await new Promise((resolve) => setTimeout(resolve, 0))
        await wrapper.vm.$nextTick()

        expect(wrapper.text()).toContain('No results found')
    })

    it('shows results list when results are returned', async () => {
        const mockData = {
            count: 2,
            size: 10,
            page: 1,
            rows: [
                {
                    title: 'Getting Started',
                    slug: 'getting-started',
                    route: '/6.0/getting-started',
                    snippet: 'Learn how to get started',
                    score: 0.9,
                    version: '6.0'
                },
                {
                    title: 'Installation',
                    slug: 'installation',
                    route: '/6.0/installation',
                    snippet: 'Install UserFrosting',
                    score: 0.8,
                    version: '6.0'
                }
            ]
        }
        vi.mocked(axios.get).mockResolvedValue({ data: mockData })

        const wrapper = mount(SearchComponent)
        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('start')

        await new Promise((resolve) => setTimeout(resolve, 0))
        await wrapper.vm.$nextTick()

        const listItems = wrapper.findAll('li')
        expect(listItems.length).toBe(2)
        expect(wrapper.text()).toContain('Getting Started')
        expect(wrapper.text()).toContain('Installation')
    })

    it('shows error message when the API call fails', async () => {
        vi.mocked(axios.get).mockRejectedValue({ response: { data: 'Server error' } })

        const wrapper = mount(SearchComponent)
        const searchInput = wrapper.find('input[autofocus]')
        await searchInput.setValue('test')

        await new Promise((resolve) => setTimeout(resolve, 0))
        await wrapper.vm.$nextTick()

        expect(wrapper.find('.uk-alert-danger').exists()).toBe(true)
    })
})
