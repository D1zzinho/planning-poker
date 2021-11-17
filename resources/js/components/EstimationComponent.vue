<template>

    <div class="estimations-box" v-if="estimationsLoaded">

        <div class="action-box mt-2" v-if="user.id === session.user_id">
            <input
                id="jira-task-id-input"
                class="form-control"
                v-bind:class="errors && errors.task ? 'border-danger' : ''"
                type="text"
                placeholder="Jira Task Id"
            />
            <div class="text-danger form-error ml-1" v-if="errors && errors.task">
                {{ errors.task[0] }}
            </div>

            <button class="btn btn-outline-info" @click="startEstimation">
                Start estimation
            </button>
        </div>

        <div class="estimation-board" v-if="estimating">

            <!-- TODO get data from jira api -->
            <div class="alert border-info my-3 d-flex" role="alert">
                <span class="h3 text-info my-auto">Estimating task <strong>{{ estimation.task }}</strong></span>
                <button
                    v-if="estimation.status === 'closed'"
                    class="btn btn-outline-dark ml-auto"
                    @click="estimation = null; estimating = false"
                >
                    Hide
                </button>
                <button
                    v-if="estimation.status !== 'closed' && isOwner"
                    class="btn btn-danger ml-auto"
                    @click="closeEstimation"
                >
                    Close
                </button>
            </div>

            <div class="py-5 mb-2 mx-auto bg-light rounded-3">
                <div class="container-fluid">
                    <div id="votes" class="votes">
                        <div v-if="estimation.status === 'open'" class="wrapper" v-for="user in users">
                            <div
                                class="vote-card"
                                v-bind:class="checkIfUserHasVoted(user.id) ? 'voted' : ''"
                            >
                                <div class="front"></div>
                                <div class="back">
                                    <span class="text-light font-weight-bolder"></span>
                                </div>
                            </div>
                            <div class="name">{{ user.name }}</div>
                        </div>

                        <div v-if="estimation.status !== 'open'" class="wrapper" v-for="vote in estimation.votes">
                            <div class="vote-card voted">
                                <div class="front"></div>
                                <div class="back finished">
                                    <span class="text-light font-weight-bolder">
                                        {{ vote.points }}
                                    </span>
                                </div>
                            </div>
                            <div class="name">{{ vote.user.name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div class="votes-show text-center">
                    <div class="row">
                        <div class="col-4" v-if="estimation.points !== null">
                            Result {{ estimation.points }}
                        </div>
                        <div class="col-4" v-else-if="estimation.original_result !== null">
                            Result {{ estimation.original_result }}
                        </div>
                        <div class="col-4" v-else></div>

                        <div class="col-4" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="finishEstimation"
                                v-bind:disabled="estimation.votes.length === 0 || estimation.status !== 'open'"
                            >Show votes</button>
                        </div>
                        <div class="col-4" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-warning"
                                v-bind:disabled="estimation.votes.length === 0 || estimation.status !== 'finished'"
                            >Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div id="vote" class="vote">
                    <div
                        v-for="value in possibleVotes"
                        class="vote-card"
                        @click="doVote(value)">
                        <div class="front">{{ value }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="estimation-list mt-2" v-if="estimationsLoaded">
            <button
                class="btn btn-primary my-2"
                type="button"
                data-toggle="collapse"
                data-target="#showEstimations"
                aria-expanded="false"
                aria-controls="showEstimations"
            >
                Estimating history
            </button>
            <div class="collapse" id="showEstimations">
                <div class="list-group" v-if="estimations && estimations.data.length > 0">
                    <a
                        role="button"
                        class="list-group-item list-group-item-action"
                        v-for="estimate in estimations.data"
                        v-bind:class="getAlertStyleByEstimationStatus(estimate)"
                        @click="estimation = estimate; estimating = true"
                    >
                        Estimation {{ estimate.task }} finished at {{ formatDate(estimate.updated_at) }}
                    </a>

                    <pagination
                        class="my-2"
                        align="center"
                        :data="estimations"
                        @pagination-change-page="getEstimations"
                    ></pagination>
                </div>
                <div v-else class="alert alert-warning">
                    No task is currently estimated in this session
                </div>
            </div>
        </div>

    </div>

</template>

<script>
import pagination from 'laravel-vue-pagination'

export default {
    name: "EstimationComponent",

    components: {
        pagination
    },

    props: [
        'user',
        'users',
        'session',
        'isOwner'
    ],

    data() {
        return {
            possibleVotes: [1, 2, 3, 5, 8, 13],
            estimations: null,
            estimationsLoaded: false,
            estimation: null,
            estimating: false,
            points: 0,
            finished: false,
            errors: {}
        }
    },

    created() {
        this.getEstimations();

        Echo.join(`game-${this.session.hash_id}`)
            .listen('StartEstimationEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    res.estimation.votes = [];
                    this.estimation = res.estimation;
                    this.estimating = true;
                    this.getEstimations();
                }
            })
            .listen('UpdateEstimationEvent', res => {
                console.log(res);
                if (res.estimation.game.user_id === this.session.user_id) {
                    this.estimation = res.estimation;
                    this.finished = true;
                    this.getEstimations();
                }
            })
            .listen('ReopenEstimateEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    this.estimation = res.estimation;
                }
            })
            .listen('VoteEvent', res => {
                if (res.vote) {
                    const index = this.estimation.votes.findIndex(vote => {
                        return vote.user_id === res.vote.user_id;
                    });

                    if (index === -1) {
                        this.estimation.votes.push(res.vote);
                    }
                }
            })
            .listenForWhisper('show-votes', response => {
                this.finished = response;
            })
    },

    mounted() {

    },

    methods: {
        async getEstimations(page = 1) {
            const response = await axios.get(`/game/${this.session.hash_id}/estimation?page=${page}`);
            this.estimations = response.data;

            // if (response.data.data.length > 0 && response.data.data[0].status !== 'closed') {
            //    this.estimation = response.data.data[0];
            //    this.estimating = true;
            // } else {
            //    this.estimation = null;
            //    this.estimating = false;
            // }

            this.estimationsLoaded = true;
        },

        async startEstimation() {
            this.errors = {};
            const jiraTaskId = document.getElementById('jira-task-id-input').value;
            try {
                await axios.post(`/game/${this.session.hash_id}/estimation`, {
                    game_id: this.session.id,
                    task: jiraTaskId
                });
            } catch (e) {
                this.errors = e.response.data.errors;
            }
        },

        async finishEstimation() {
            if (this.estimation.status === 'open') {
                this.errors = {};
                try {
                    await axios.patch(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/finish`, {
                        original_result: this.calculateResult()
                    });
                } catch (e) {
                    this.errors = e.response.data.errors;
                }
            }
        },

        async closeEstimation() {
            this.errors = {};
            try {
                await axios.patch(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/close`, {
                    points: this.points
                });
            } catch (e) {
                this.errors = e.response.data.errors;
            }
        },

        async getVotesToEstimation(estimationId = this.estimation.id) {
            const response = await axios.get(`/vote/all-to-estimation/${estimationId}`);
            response.data.forEach(vote => {
                this.checkIfUserHasVoted(vote.user_id);
            });
            return response.data;
        },

        async doVote(points = 1) {
            const index = this.estimation.votes.findIndex(vote => {
                return vote.user_id === this.user.id;
            });

            if (index === -1) {
                const body = {
                    estimation_id: this.estimation.id,
                    points: points
                }
                try {
                    await axios.post(`/vote`, body);
                    // Echo.join(`game-${this.session.hash_id}`).whisper('voting', await this.getVotesToEstimation());
                } catch (e) {
                    console.log(e);
                }
            }
        },

        checkIfUserHasVoted(userId) {
            const index = this.estimation.votes.findIndex(vote => vote.user_id === userId);
            return index !== -1;
        },

        getUserVote(userId) {
            const index = this.estimation.votes.findIndex(vote => vote.user_id === userId);
            return this.estimation.votes[index].points;
        },

        getAlertStyleByEstimationStatus(estimate) {
            if (this.estimation !== null && estimate.id === this.estimation.id) {
                return 'active';
            } else if (estimate.status === 'finished') {
                return 'list-group-item-warning';
            } else if (estimate.status === 'closed') {
                return 'list-group-item-success';
            }

            return '';
        },

        calculateResult() {
            let sum = 0;
            this.estimation.votes.forEach(vote => {
                sum += vote.points;
            });

            return Math.round((sum / this.estimation.votes.length) * 10) / 10;
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
