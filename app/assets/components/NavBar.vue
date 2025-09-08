<script setup lang="ts">
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
const config = useConfigStore()
</script>

<template>
    <UFNavBar :title="config.get('site.title')" :to="{ name: 'home' }">
        <UFNavBarItem :to="{ name: 'about' }" :label="$t('ABOUT')" />
    </UFNavBar>
</template>
