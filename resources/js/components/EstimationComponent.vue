<template>

    <div class="estimations-box">

        <div class="input-group mt-3 mt-lg-0">
            <input
                id="jira-task-id-input"
                class="form-control"
                v-bind:class="errors && errors.task ? 'border-danger' : ''"
                type="text"
                placeholder="Jira Task Id"
            />
            <div class="input-group-append">
                <button
                    class="btn btn-outline-info"
                    type="button"
                    @click="startEstimation"
                >
                    Start
                </button>
            </div>
        </div>
        <div class="text-danger form-error ml-1 mb-3" v-if="errors && errors.task">
            {{ errors.task[0] }}
        </div>

        <div class="estimation-board" v-if="estimating">

            <!-- TODO get data from jira api -->
            <div class="alert border-info my-3 d-flex" role="alert">
                <span class="h3 text-info my-auto">Estimating task <strong>{{ estimation.task }}</strong></span>
                <button
                    class="btn btn-outline-dark ml-auto"
                    @click="hideEstimation"
                >
                    Hide
                </button>
                <button
                    v-if="estimation.status === 'finished' && isOwner"
                    class="btn btn-danger ml-2"
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
                        <div class="col-4 result-box" v-if="estimation.points !== null">
                            <span>Result</span><br>
                            <span class="h3">{{ estimation.points }}</span>
                        </div>
                        <div class="col-4 result-box" v-else-if="estimation.original_result !== null">
                            <span>Result</span><br>
                            <span class="h3">{{ estimation.original_result }}</span>
                        </div>
                        <div class="col-4 m-auto" v-else></div>

                        <div class="col-4 m-auto" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="finishEstimation"
                                v-bind:disabled="estimation.votes.length === 0 || estimation.status !== 'open'"
                            >Show votes</button>
                        </div>
                        <div class="col-4 m-auto" v-if="isOwner">
                            <button
                                type="button"
                                class="btn btn-warning"
                                @click="resetEstimation"
                                v-bind:disabled="estimation.votes.length === 0 || estimation.status !== 'finished'"
                            >Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div class="vote">
                    <div v-for="value in possibleVotes" class="vote-card" @click="doVote(value)">
                        <div class="front" v-bind:class="estimation.status !== 'open' ? 'disabled' : ''">
                            {{ value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
export default {
    name: "EstimationComponent",

    props: [
        'user',
        'users',
        'session',
        'isOwner',
        'estimation',
        'estimating'
    ],

    data() {
        return {
            possibleVotes: [1, 2, 3, 5, 8, 13],
            points: 0, // TODO: Ask for final result then save it and close estimation.
            finished: false,
            errors: {}
        }
    },

    created() {
        Echo.join(`game-${this.session.hash_id}`)
            .listen('StartEstimationEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    res.estimation.votes = [];
                    this.$emit('update', { estimation: res.estimation, estimating: true });
                }
            })
            .listen('UpdateEstimationEvent', res => {
                if (res.estimation.game.user_id === this.session.user_id) {
                    this.$emit('update', { estimation: res.estimation, estimating: true });
                    this.finished = res.estimation.status !== 'open';
                }
            })
            .listen('VoteEvent', res => {
                // const index = this.estimation.votes.findIndex(vote => {
                //     return vote.user_id === res.vote.user_id;
                // });
                //
                // if (index === -1) {
                    this.$emit('push', res.vote);
                // }
            })
    },

    methods: {
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

        async resetEstimation() {
            if (this.estimation.status !== 'closed') {
                this.errors = {};
                try {
                    await axios.post(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/reset`);
                } catch (e) {
                    this.errors = e.response.data.errors;
                }
            }
        },

        async closeEstimation() {
            if (this.estimation.status === 'finished') {
                this.errors = {};
                try {
                    await axios.patch(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/close`, {
                        points: this.points
                    });
                } catch (e) {
                    this.errors = e.response.data.errors;
                }
            }
        },

        hideEstimation() {
            this.$emit('update', { estimation: null, estimating: false });
        },

        async getVotesToEstimation(estimationId = this.estimation.id) {
            const response = await axios.get(`/vote/all-to-estimation/${estimationId}`);
            response.data.forEach(vote => {
                this.checkIfUserHasVoted(vote.user_id);
            });
            return response.data;
        },

        async doVote(points = 1) {
            // const card = document.getElementById(`card_${points}`);
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
                    // card.classList.add('selected');
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

        calculateResult() {
            let sum = 0;
            this.estimation.votes.forEach(vote => {
                sum += vote.points;
            });

            return Math.round((sum / this.estimation.votes.length) * 10) / 10;
        }
    }
}
</script>
