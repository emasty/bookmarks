<!--
  - Copyright (c) 2020. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->

<template>
	<Item :selectable="false"
		:renaming="true"
		title=""
		:editable="true"
		:rename-placeholder="t('bookmarks', 'Enter a title')"
		select-label=""
		@rename="submit"
		@rename-cancel="cancel">
		<template #icon>
			<FolderIcon :fill-color="colorPrimaryElement" class="icon" />
		</template>
	</Item>
</template>
<script>
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import Item from './Item.vue'
import { actions, mutations } from '../store/index.js'

export default {
	name: 'CreateFolder',
	components: { Item, FolderIcon },
	computed: {
		loading() {
			return this.$store.state.loading.createFolder
		},
	},
	methods: {
		submit(title) {
			const parentFolder = this.$route.params.folder
			this.$store.dispatch(actions.CREATE_FOLDER, {
				parentFolder,
				title,
			})
		},
		cancel() {
			this.$store.commit(
				mutations.DISPLAY_NEW_FOLDER,
				false
			)
		},
	},
}
</script>
<style scoped>
.icon {
	flex-grow: 0;
	height: 20px;
	width: 20px;
	background-size: cover;
	margin: 0 15px;
	cursor: pointer;
}

.item--gridview .icon {
	background-size: cover;
	position: absolute;
	top: 20%;
	left: calc(45% - 50px);
	transform: scale(4);
	transform-origin: top left;
}
</style>
