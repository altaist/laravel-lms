<template>
    <div>
        <page-header
            :title="title"
            :left-btn-icon="leftBtnIcon"
            :left-btn-route="leftBtnRoute"
            :left-btn-go-back="leftBtnGoBack"
            :right-btn-icon="rightBtnIcon"
            :right-btn-route="rightBtnRoute"

            @click:left="emit('click:header:left')"
            @click:right="emit('click:header:right')"
            />

        <page-container class="q-px-md" style="margin-bottom: 100px;">    
            <slot/>
        </page-container>

        <page-footer 
            v-if="showFooter"
            :title="footerText"
            :is-sticky="true"
            :menu="footerMenu || localMenu"

            @click:left="emit('click:footer:left')"
            @click:right="emit('click:footer:right')"
            @click:menu="emit('click:footer:menu', $event)"
        />
    </div>
</template>
<script setup>
import PageContainer from '@/components/shared/PageContainer.vue';


const props = defineProps({
    title: {
        type: String,
        default: 'ПРИЛОЖЕНИЕ'
    },
    level: {
        type: Number,
        default: 0
    },
    showFooter: {
        type: Boolean,
        default: false
    },
    footerText: {
        type: String,
        default: 'Контакты'
    },
    footerMenu: {
        type: Array,
        default: null
    },

    leftBtnIcon: {
        type: String,
        default: "fa-solid fa-home"
    },
    leftBtnRoute: {
        type: String,
        default: ''
    },
    leftBtnGoBack: {
        type: Boolean,
        default: false
    },
    rightBtnIcon: {
        type: String,
        default: null
    },
    rightBtnRoute: {
        type: String,
        default: 'dashboard'
    },
});

const emit = defineEmits(['click:header:left', 'click:header:right', 'click:header:title', 'click:footer:left', 'click:footer:right', 'click:footer:title', 'click:footer:menu']);

const model = defineModel({
    type: String,
});

const localMenu = [
    {
        id: 1,
        label: 'Menu1',
        icon: 'fa-solid fa-home'
    },
    {
        id: 2,
        label: 'Menu2',
        icon: 'fa-solid fa-shop'
    },
    {
        id: 3,
        label: 'Menu3',
        icon: 'fa-solid fa-user'
    }
];

</script>
