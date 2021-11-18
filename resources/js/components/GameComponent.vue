<template>
    <div class="row">

        <estimation
            class="col-md-8"
            :user="user"
            :users="users"
            :session="session"
            :estimation="estimation"
            :estimating="estimating"
            :isOwner="isOwner"
            @update="estimation = $event.estimation; estimating = $event.estimating; getEstimations()"
        ></estimation>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Active users
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex" v-for="user in users">
                        {{ user.name }} <span v-if="user.id === session.user_id" class="text-info ml-auto">owner</span>
                    </li>
                </ul>
            </div>

            <div class="estimation-list mt-2" v-if="estimationsLoaded">
                <button
                    class="btn btn-primary my-2 w-100"
                    type="button"
                    data-toggle="collapse"
                    data-target="#showEstimations"
                    aria-expanded="false"
                    aria-controls="showEstimations"
                >
                    Estimating history
                </button>
                <div class="collapse" id="showEstimations">
                    <div class="list-group" v-if="estimations && estimations.length > 0">
                        <a
                            role="button"
                            class="list-group-item list-group-item-action"
                            v-for="estimate in estimations"
                            v-bind:class="getColorStyleByEstimationStatus(estimate)"
                            @click="estimation = estimate; estimating = true"
                        >
                            Estimation {{ estimate.task }}
                            <span v-if="estimate.status !== 'open'">
                                finished at {{ formatDate(estimate.updated_at) }}
                            </span>
                            <span v-else>in progress</span>
                        </a>

                        <pagination
                            v-model="page"
                            :per-page="perPage"
                            :records="estimations.length"
                        />
                    </div>
                    <div v-else class="alert alert-warning">
                        No task is currently estimated in this session
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import Pagination from 'vue-pagination-2';

export default {
    components: {
        Pagination
    },

    props: [
        'user',
        'session'
    ],

    data() {
        return {
            page: 1,
            perPage: 8,
            users: [],
            isOwner: false,
            estimations: [],
            estimation: null,
            estimating: false,
            estimationsLoaded: false
        }
    },

    created() {
        this.checkIfUserIsGameOwner();
        this.getEstimations();

        Echo.join('game-' + this.session.hash_id)
            .here(user => {
                this.users = user;
            })
            .joining(user => {
                this.users.push(user);
            })
            .leaving(user => {
                this.users = this.users.filter(u => {
                    return u.id !== user.id;
                })
            })
    },

    methods: {
        async getEstimations() {
            const response = await axios.get(`/game/${this.session.hash_id}/estimation`);
            this.estimations = response.data;
            this.estimationsLoaded = true;
        },

        updateEstimations(event) {
            console.log(event)
        },

        getColorStyleByEstimationStatus(estimate) {
            if (this.estimation !== null && estimate.id === this.estimation.id) {
                return 'active';
            } else if (estimate.status === 'finished') {
                return 'list-group-item-warning';
            } else if (estimate.status === 'closed') {
                return 'list-group-item-success';
            }

            return '';
        },

        checkIfUserIsGameOwner() {
            this.isOwner = this.user.id === this.session.user_id;
        },

        formatDate(dateToFormat) {
            const date = new Date(dateToFormat);

            const day = date.getDate() >= 10 ? date.getDate() : `0${date.getDate()}`;
            const month = (date.getMonth() + 1) >= 10 ? (date.getMonth()+1) : `0${(date.getMonth()+1)}`;
            const year = date.getFullYear();
            const hour = date.getHours() >= 10 ? date.getHours() : `0${date.getHours()}`;
            const min = date.getMinutes() >= 10 ? date.getMinutes() : `0${date.getMinutes()}`;

            return day + "-" + month + "-" + year + " " + hour + ":" + min;
        }
    }
}
</script>
