<template>

    <div class="estimations-box">

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
                    v-if="estimating && estimation.status === 'open'"
                    class="btn btn-danger ml-auto"
                    @click="finishEstimation"
                >
                    Finish
                </button>
            </div>

            <div class="py-5 mb-5 mx-auto bg-light rounded-3">
                <div class="container-fluid mb-5">
                    <div id="votes" class="votes">
                        <div class="wrapper" v-for="user in users">
                            <div class="vote-card" v-bind:class="user.voted ? 'voted' : ''">
                                <div class="front" data-value=""></div>
                                <div class="back"></div>
                            </div>
                            <div class="name">{{ user.name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div class="votes-show" style="text-align: center">
                    <div class="row">
                        <div class="col-4" v-if="points !== null">
                            Result
                            <div id="vote-result">{{ points }}</div>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-lg btn-info">Show votes</button>
                            <button type="button" class="btn btn-lg btn-danger">Reset</button>
                        </div>
                        <div class="col-4">

                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-5">
                <div id="vote" class="vote">
                    <div class="vote-card" data-value="1" @click="user.voted = true">
                        <div class="front">1</div>
                    </div>
                    <div class="vote-card" data-value="2">
                        <div class="front">2</div>
                    </div>
                    <div class="vote-card" data-value="3">
                        <div class="front">3</div>
                    </div>
                    <div class="vote-card" data-value="5">
                        <div class="front">5</div>
                    </div>
                    <div class="vote-card" data-value="8">
                        <div class="front">8</div>
                    </div>
                    <div class="vote-card" data-value="13">
                        <div class="front">13</div>
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

    components:{
        pagination
    },

    props: [
        'user',
        'users',
        'session'
    ],

    data() {
        return {
            estimations: {},
            estimationsLoaded: false,
            estimation: null,
            estimating: false,
            points: 5,
            errors: {}
        }
    },

    created() {
        Echo.join(`game-${this.session.hash_id}`)
            .listen('StartEstimationEvent', (event) => {
                if (event.estimation.game.user_id === this.session.user_id) {
                    this.estimation = event.estimation;
                    this.estimating = true;
                }
            })
            .listen('FinishEstimationEvent', (event) => {
                if (event.estimation.game.user_id === this.session.user_id) {
                    // const index = this.estimations.data.findIndex(estimate => estimate.id === event.estimation.id);
                    // this.estimations.data[index] = event.estimation;
                    this.getEstimations();
                }
            })
    },

    mounted() {
        this.getEstimations();
    },

    methods: {
        async getEstimations(page = 1) {
            const response = await axios.get(`/game/${this.session.hash_id}/estimation?page=${page}`);
            this.estimations = response.data;
            if (response.data.data[0].status === 'open') {
                this.estimation = response.data.data[0];
                this.estimating = true;
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
                'points': this.points
            });
        }
    }
}
</script>
