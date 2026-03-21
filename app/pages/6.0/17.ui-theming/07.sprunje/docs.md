---
title: Sprunjes
description: Connect table UIs to server-side filtering, sorting, and pagination with UserFrosting Sprunjes.
---

When your data grows beyond what a client-side table can handle comfortably, it's time for a [Sprunje](/database/data-sprunjing). Sprunjes are UserFrosting's built-in system for server-driven data tables: the backend handles filtering, sorting, and pagination, and the frontend renders the results.

The name is quirky, but the concept is practical. Instead of fetching all your records upfront (which gets slow fast), you fetch only what the current page needs — filtered, sorted, and paginated by the server. The backend returns a predictable JSON shape, and Pink-Cupcake's Sprunje components consume it automatically.

## What a Sprunje Solves

A client-side table built with `v-for` works great for small datasets. But once you have thousands of records, loading them all at once becomes a performance problem — long wait times on initial load, large memory footprint in the browser, and slow client-side filtering.

A Sprunje moves all of that work to the backend:

- **Filtering** based on user-provided search terms.
- **Sorting** by any registered field.
- **Pagination** with a configurable page size.
- **A stable JSON response shape** — `count`, `count_filtered`, `count_page`, `rows` — that the frontend components know how to read.

## The UI Side: Pink-Cupcake Sprunje Components

Pink-Cupcake provides a complete set of components for Sprunje-powered pages. They're designed to work together, passing shared state behind the scenes so you don't have to manually wire up filtering, sorting, and pagination interactions:

- `UFSprunjeTable` — the root component that fetches data from the API and provides shared state to all children.
- `UFSprunjeHeader` — a `<th>` cell for the table header. When given a `sort` prop, it becomes a clickable sortable column.
- `UFSprunjeColumn` — a `<td>` cell for each row in the table body.
- `UFSprunjeSearch` — a search input that filters the table by a specific column.
- `UFSprunjeFilters` — renders filter controls for all fields declared as filterable by the backend Sprunje class.
- `UFSprunjePaginator` — pagination controls that respond to the `count` fields in the API response.

Because these components share their state automatically, activating a search filter will reset pagination to page one without you writing that logic manually.

Check out the [components overview](/ui-theming/components-overview#sprunje-data-tables) page for details on how to use each component and customize their appearance.

## The Backend Side

Your PHP Sprunje class extends `UserFrosting\Sprunjify\Sprunje`, defines a base query, and registers the fields that can be filtered and sorted. Your API endpoint calls the Sprunje and returns its JSON response. The Pink-Cupcake components consume that response automatically.

> [!TIP]
> The backend Sprunje system is covered in the data access and API chapters. This page focuses on the frontend components. If you haven't used Sprunjes on the backend yet, you'll want to read those chapters before building the full stack connection here.
