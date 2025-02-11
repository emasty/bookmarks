<!--
  - Copyright (c) 2020. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->

<template>
	<div class="bookmarkslist__emptyBookmarks">
		<NcEmptyContent v-if="$route.name === routes.ARCHIVED"
			:title="t('bookmarks', 'No bookmarked files')"
			:description="t('bookmarks', 'Bookmarks to files like photos or PDFs will automatically be saved to your Nextcloud files, so you can still find them even when the link goes offline.')">
			<template #icon>
				<FileDocumentMultipleIcon />
			</template>
		</NcEmptyContent>
		<NcEmptyContent v-else-if="$route.name === routes.UNAVAILABLE"
			:title="t('bookmarks', 'No broken links')"
			:description="t('bookmarks', 'Bookmarked links are checked regularly and the ones that cannot be reached are listed here.')">
			<template #icon>
				<LinkVariantOffIcon />
			</template>
		</NcEmptyContent>
		<NcEmptyContent v-else-if="$route.name === routes.SHARED_FOLDERS"
			:title="t('bookmarks', 'No shared folders')"
			:description="t('bookmarks', 'You can share bookmark folders with others. All folders shared with you are listed here.')">
			<template #icon>
				<ShareVariantIcon />
			</template>
		</NcEmptyContent>
		<NcEmptyContent v-else-if="$route.name === routes.DUPLICATED"
			:title="t('bookmarks', 'No duplicated bookmarks')"
			:description="t('bookmarks', 'One bookmark can be in multiple folders at once. Updating it will update all copies. All duplicated bookmarks are listed here for convenience.')">
			<template #icon>
				<VectorLinkIcon />
			</template>
		</NcEmptyContent>
		<NcEmptyContent v-else
			:title="t('bookmarks', 'No bookmarks here')"
			:description="t('bookmarks', 'Add bookmarks manually or import bookmarks from a HTML file.')">
			<template #icon>
				<StarShootingIcon />
			</template>
			<template v-if="!isPublic" #action>
				<input ref="import"
					type="file"
					class="import"
					size="5"
					@change="onImportSubmit">
				<NcButton @click="onCreateOpen">
					<template #icon>
						<PlusIcon />
					</template>
					{{ t('bookmarks', 'Add a bookmark') }}
				</NcButton>
				<NcButton @click="onImportOpen">
					<template #icon>
						<UploadIcon v-if="!importing" />
						<NcLoadingIcon v-else />
					</template>
					{{ t('bookmarks', 'Import bookmarks') }}
				</NcButton>
			</template>
		</NcEmptyContent>
	</div>
</template>

<script>
import { NcEmptyContent, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import { actions, mutations } from '../store/index.js'
import { privateRoutes } from '../router.js'
import StarShootingIcon from 'vue-material-design-icons/StarShooting.vue'
import UploadIcon from 'vue-material-design-icons/Upload.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'
import ShareVariantIcon from 'vue-material-design-icons/ShareVariant.vue'
import VectorLinkIcon from 'vue-material-design-icons/VectorLink.vue'
import LinkVariantOffIcon from 'vue-material-design-icons/LinkVariantOff.vue'
import FileDocumentMultipleIcon from 'vue-material-design-icons/FileDocumentMultiple.vue'

export default {
	name: 'NoBookmarks',
	components: { NcEmptyContent, StarShootingIcon, NcButton, NcLoadingIcon, UploadIcon, PlusIcon, ShareVariantIcon, VectorLinkIcon, LinkVariantOffIcon, FileDocumentMultipleIcon },
	data() {
		return { importing: false }
	},
	computed: {
		routes() {
			return privateRoutes
		},
	},
	methods: {
		onCreateOpen() {
			this.$store.commit(mutations.DISPLAY_NEW_BOOKMARK, true)
		},
		onImportOpen() {
			this.$refs.import.click()
		},
		async onImportSubmit(e) {
			this.importing = true
			try {
				await this.$store.dispatch(actions.IMPORT_BOOKMARKS, { file: e.target.files[0], folder: this.$route.params.folder || -1 })
			} catch (e) {
				console.warn(e)
			}
			this.importing = false
		},
	},
}
</script>
<style scoped>
.bookmarkslist__emptyBookmarks {
	width: 500px;
	margin: 0 auto;
}

.import {
	opacity: 0;
	position: absolute;
	top: 0;
	left: -1000px;
}

button {
	margin-bottom: 15px;
}
</style>
