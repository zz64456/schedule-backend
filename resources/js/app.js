import './bootstrap';
import { createApp } from 'vue';
import SchedulePage from './components/SchedulePage.vue';

const app = createApp({});

app.component('schedule-page', SchedulePage);

app.mount('#app');
