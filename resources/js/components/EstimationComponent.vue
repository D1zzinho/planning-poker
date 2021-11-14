<template>

    <div class="estimations-box" v-if="estimationsLoaded">

        <div class="action-box mt-2" v-if="user.id === session.user_id">
            <input
                v-if="!estimating"
                id="jira-task-id-input"
                class="form-control"
                v-bind:class="errors && errors.task ? 'border-danger' : ''"
                type="text"
                placeholder="Jira Task Id"
            />
            <div class="text-danger form-error ml-1" v-if="errors && errors.task">
                {{ errors.task[0] }}
            </div>

            <button v-if="!estimating" class="btn btn-outline-info" @click="startEstimation">
                Start estimation
            </button>
        </div>

        <div class="estimation-board" v-if="estimating">

            <!-- TODO get data from jira api -->
            <div class="alert border-info my-3 d-flex" role="alert">
                <span class="h3 text-info my-auto">Estimating task <strong>{{ estimation.task }}</strong></span>
                <button
                    v-if="estimation.status === 'open' && isOwner"
                    class="btn btn-danger ml-auto"
                    @click="finishEstimation"
                >
                    Finish
                </button>
            </div>

            <div class="py-5 mb-2 mx-auto bg-light rounded-3">
                <div class="container-fluid">
                    <div id="votes" class="votes">
                        <div class="wrapper" v-for="user in users">
                            <div
                                class="vote-card"
                                v-bind:class="checkIfUserDidVote(user.id) ? 'voted' : ''"
                            >
                                <div class="front"></div>
                                <div class="back" v-bind:class="finished ? 'finished' : ''">
                                    <span class="text-light font-weight-bolder" v-if="finished">
                                        {{ getUserVote(user.id) }}
                                    </span>
                                    <span v-else></span>
                                </div>
                            </div>
                            <div class="name">{{ user.name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div class="votes-show text-center">
                    <div class="row">
                        <div class="col-4" v-if="points !== null">
                            Result
                            <div id="vote-result">{{ points }}</div>
                        </div>
                        <div class="col-4" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="showVotes"
                                v-bind:disabled="this.users.length !== this.votes.length"
                            >Show votes</button>
                        </div>
                        <div class="col-4" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-warning"
                                v-bind:disabled="this.votes.length === 0"
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
                All estimations
            </button>
            <div class="collapse" id="showEstimations">
                <div class="list-group" v-if="estimations && estimations.data.length > 0">
                    <a
                        role="button"
                        class="list-group-item list-group-item-action"
                        v-for="estimate in estimations.data"
                        v-bind:class="[
                            estimate === estimation ? 'active' : estimate.status === 'closed' ? 'list-group-item-success' : ''
                        ]"
                        @click="estimation = estimate; estimating = true"
                    >
                        Estimation for {{ estimate.task }}
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
            estimations: {},
            estimationsLoaded: false,
            estimation: null,
            estimating: false,
            votes: [],
            points: 5, // TODO estimation result calculation
            finished: false,
            errors: {}
        }
    },

    created() {
        Echo.join(`game-${this.session.hash_id}`)
            .listen('StartEstimationEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    this.estimation = res.estimation;
                    this.estimating = true;
                }
            })
            .listen('FinishEstimationEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    // const index = this.estimations.data.findIndex(estimate => estimate.id === event.estimation.id);
                    // this.estimations.data[index] = event.estimation;
                    this.getEstimations();
                }
            })
            .listen('VoteEvent', res => {
                if (res.vote) {
                    const index = this.votes.findIndex(vote => {
                        return vote.user_id === res.vote.user_id;
                    });

                    if (index === -1) {
                        this.votes.push(res.vote);
                    }
                }
            })
            .listenForWhisper('voting', response => {
                this.votes = response;
            })
            .listenForWhisper('show-votes', response => {
                this.finished = response;
            })
    },

    mounted() {
        this.getEstimations();
    },

    methods: {
        async getEstimations(page = 1) {
            const response = await axios.get(`/game/${this.session.hash_id}/estimation?page=${page}`);
            this.estimations = response.data;

            if (response.data.data.length > 0 && response.data.data[0].status === 'open') {
                this.estimation = response.data.data[0];
                this.estimating = true;
                this.votes = await this.getVotesToEstimation();
            } else {
                this.estimation = null;
                this.estimating = false;
            }

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
            await axios.post(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/finish`, {
                points: this.points
            });
        },

        async getVotesToEstimation(estimationId = this.estimation.id) {
            const response = await axios.get(`/vote/all-to-estimation/${estimationId}`);
            response.data.forEach(vote => {
                this.checkIfUserDidVote(vote.user_id);
            });
            return response.data;
        },

        async doVote(points = 1) {
            const index = this.votes.findIndex(vote => {
                return vote.user_id === this.user.id;
            });

            if (index === -1) {
                const body = {
                    estimation_id: this.estimation.id,
                    points: points
                }
                try {
                    await axios.post(`/vote`, body);
                    this.votes = await this.getVotesToEstimation();
                    Echo.join(`game-${this.session.hash_id}`).whisper('voting', this.votes);
                } catch (e) {
                    console.log(e);
                }
            }
        },

        checkIfUserHasVoted(userId) {
            const index = this.votes.findIndex(vote => vote.user_id === userId);
            return index !== -1;
        },

        getUserVote(userId) {
            const index = this.votes.findIndex(vote => vote.user_id === userId);
            return this.votes[index].points;
        },

        showVotes() {
            if (this.users.length === this.votes.length) {
                this.finished = true;
                Echo.join(`game-${this.session.hash_id}`).whisper('show-votes', this.finished);
            }
        }
    }
}
</script>
