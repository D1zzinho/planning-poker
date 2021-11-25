<template>

    <div class="estimations-box">

        <div v-if="session.user_id === user.id">
            <h2>Start new estimation</h2>
            <div class="input-group mt-lg-0">
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
        </div>

        <div class="estimation-board" v-if="estimating">
            <!-- TODO get data from jira api -->
            <div class="alert border-info d-flex" role="alert" v-bind:class="session.user_id === user.id ? 'my-3' : ''">
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
                    v-b-modal.save-points
                >
                    Close
                </button>

                <b-modal
                    v-if="estimation.status === 'finished' && isOwner"
                    id="save-points"
                    centered
                    title="Decide how many points and close estimation"
                    ok-variant="success"
                    cancel-variant="danger"
                    body-bg-variant="light"
                    @ok="handleCloseEstimation"
                    @show="errors.points = null; isInputVisible = false"
                    @hidden="errors.points = null; isInputVisible = false"
                >
                    <div class="text-center">
                        <h3 class="estimation-result points">
                            <span
                                @click="isInputVisible = !isInputVisible"
                                v-if="!isInputVisible"
                            >
                                {{ estimation.original_result }}
                            </span>
                            <input
                                id="save-points-input"
                                v-else
                                v-bind:class="errors && errors.points ? 'border-danger' : ''"
                                class="form-control input"
                                type="number"
                                min="1"
                                max="13"
                                v-bind:value="getClosestValue(estimation.original_result)"
                            />
                        </h3>
                        <div class="text-danger form-error mt-2" v-if="errors && errors.points">
                            {{ errors.points[0] }}
                        </div>
                    </div>
                </b-modal>
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
                        <div class="col-4 m-auto" v-if="estimation.points !== null">
                            <h3 class="estimation-result final">{{ estimation.points }}</h3>
                        </div>
                        <div class="col-4 m-auto" v-else-if="estimation.original_result !== null">
                            <h3 class="estimation-result">{{ estimation.original_result }}</h3>
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
            finished: false,
            isInputVisible: false,
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

                    let messagePart;
                    switch (res.estimation.status) {
                        case 'open':
                            messagePart = 'restarted';
                            break;
                        case 'finished':
                            messagePart = 'finished';
                            break;
                        case 'closed':
                            messagePart = 'closed';
                            break;
                    }

                    this.makeToast(
                        `Success`,
                        `Estimation ${res.estimation.task} successfully ${messagePart}!`,
                        'success'
                    );
                }
            })
            .listen('VoteEvent', res => {
                this.$emit('push', res);
            })
    },

    methods: {
        async startEstimation() {
            this.errors = {};
            const jiraTaskId = document.getElementById('jira-task-id-input').value;
            try {
                await axios.post(`/estimation`, {
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
                    await axios.patch(`/estimation/${this.estimation.id}/finish`, {
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
                    await axios.post(`/estimation/${this.estimation.id}/reset`);
                } catch (e) {
                    this.errors = e.response.data.errors;
                }
            }
        },

        async closeEstimation() {
            this.errors = {};
            const pointsInput = document.getElementById('save-points-input');
            if (pointsInput) {
                if (this.possibleVotes.includes(Number(pointsInput.value))) {
                    try {
                        await axios.patch(`/estimation/${this.estimation.id}/close`, {
                            points: pointsInput.value
                        });
                        this.$nextTick(() => {
                            this.$bvModal.hide('modal-prevent-closing');
                        })
                    } catch (e) {
                        this.errors = e.response.data.errors;
                    }
                } else {
                    this.errors = { points: ['Incorrect story point value.'] };
                }
            } else {
                this.errors = { points: ['You have to click on result and save story points.'] };
            }
        },

        handleCloseEstimation(bvModalEvt) {
            bvModalEvt.preventDefault();
            this.closeEstimation();
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
            const index = this.estimation.votes.findIndex(vote => {
                return vote.user_id === this.user.id;
            });

            const body = {
                estimation_id: this.estimation.id,
                points: points
            }

            try {
                if (index === -1) {
                    await axios.post(`/vote`, body);
                } else {
                    await axios.patch(`/vote/${this.estimation.votes[index].id}`, body);
                }
            } catch (e) {
                this.errors = e.response.data.message;
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

        getClosestValue(points) {
            return this.possibleVotes.reduce((prev, curr) => {
                return (Math.abs(curr - points) < Math.abs(prev - points) ? curr : prev);
            });
        },

        calculateResult() {
            let sum = 0;
            this.estimation.votes.forEach(vote => {
                sum += vote.points;
            });

            return Math.round((sum / this.estimation.votes.length) * 10) / 10;
        },

        makeToast(title = '', message = '', type = null) {
            this.$bvToast.toast(message, {
                title: title,
                toaster: window.innerWidth > 991 ? 'b-toaster-bottom-right' : 'b-toaster-bottom-center',
                variant: type,
                solid: true,
                appendToast: true
            });
        }
    }
}
</script>
