<template>

    <div class="row">

        <div class="col-12 mb-2">
            <b-breadcrumb :items="locations"></b-breadcrumb>
        </div>

        <estimation
            class="col-lg-8"
            :user="user"
            :users="users"
            :session="session"
            :estimation="estimation"
            :estimating="estimating"
            :isOwner="isOwner"
            @update="estimation = $event.estimation; estimating = $event.estimating; getEstimations()"
            @push="pushVote($event)"
        ></estimation>

        <div class="col-lg-4 mt-3 mt-lg-0">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="mr-auto"></div> Room #{{ session.id }} - active users
                </div>
                <div class="spinner-border text-info my-2 mx-auto" role="status" v-if="!usersLoaded">
                    <span class="sr-only">Loading...</span>
                </div>
                <ul class="list-group list-group-flush" v-else>
                    <li class="list-group-item d-flex" v-for="user in users">
                        {{ user.name }} <span v-if="user.id === session.user_id" class="text-info ml-auto">owner</span>
                    </li>
                </ul>
            </div>

            <div class="accordion mt-3" id="estimation-list-accordion">
                <div class="card">
                    <div class="card-header">
                        <a
                            href="javascript:void(0)"
                            data-toggle="collapse"
                            data-target="#estimations"
                            aria-expanded="true"
                            aria-controls="estimations"
                        >
                            List of estimations
                        </a>
                    </div>

                    <div
                        v-if="estimationsLoaded"
                        id="estimations"
                        class="collapse show"
                        aria-labelledby="estimations"
                        data-parent="#estimation-list-accordion"
                    >
                        <div class="list-group list-group-flush" v-if="estimations && estimations.length > 0">
                            <a
                                role="button"
                                class="list-group-item list-group-item-action"
                                v-for="estimate in getEstimationsPerPage"
                                v-bind:class="getColorStyleByEstimationStatus(estimate)"
                                @click="estimation = estimate; estimating = true"
                            >
                                Estimation {{ estimate.task }}
                                <span v-if="estimate.status !== 'open'">
                                    finished at {{ formatDate(estimate.updated_at) }}
                                </span>
                                <span v-else>in progress</span>
                            </a>
                        </div>
                        <div v-else class="alert alert-warning mb-0">No estimations found.</div>

                        <div class="card-footer" v-if="estimations && estimations.length > perPage">
                            <pagination
                                class="d-flex"
                                v-model="page"
                                :per-page="perPage"
                                :records="estimations.length"
                                :options="paginateOptions"
                            />
                        </div>
                    </div>
                    <div v-else class="spinner-border text-info my-2 mx-auto" role="status">
                        <span class="sr-only">Loading...</span>
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
            locations: [
                {
                    text: 'Home',
                    href: '/home'
                },
                {
                    text: 'Lobby',
                    href: '/lobby'
                },
                {
                    text: `Game #${this.session.id}`,
                    active: true
                },
            ],
            page: 1,
            perPage: 8,
            paginateOptions: {
                chunk: 3,
                chunksNavigation: 'fixed',
                texts: {
                    count: '',
                    first: '',
                    last: ''
                }
            },
            users: [],
            usersLoaded: false,
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

        Echo.join(`game-${this.session.hash_id}`)
            .here(user => {
                this.users = user;
                this.usersLoaded = true;
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

    computed: {
        getEstimationsPerPage() {
            const startIndex = this.perPage * (this.page - 1);
            const endIndex = startIndex + this.perPage;
            return this.estimations.slice(startIndex, endIndex);
        }
    },

    watch: {
        estimation() {
            console.log(this.estimation)
        }
    },

    methods: {
        async getEstimations() {
            const response = await axios.get(`/game/${this.session.hash_id}/estimations`);
            this.estimations = response.data;
            this.estimationsLoaded = true;
        },

        pushVote(voteEvent) {
            this.getEstimations();

            if (this.estimation !== null && this.estimation.id === voteEvent.vote.estimation.id) {
                if (voteEvent.update) {
                    const index = this.estimation.votes.findIndex(vote => {
                       return vote.id === voteEvent.vote.id;
                    });

                    this.estimation.votes[index] = voteEvent.vote;
                } else {
                    this.estimation.votes.push(voteEvent.vote);
                }
            }
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
