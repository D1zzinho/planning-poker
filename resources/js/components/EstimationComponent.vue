<template>

    <div class="estimations-box">

        <div class="estimation-list mt-2">
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#showEstimations" aria-expanded="false" aria-controls="showEstimations">
                All estimations
            </button>
            <div class="collapse" id="showEstimations">
                <div class="list-group">
                    <a
                        role="button"
                        class="list-group-item list-group-item-action"
                        v-for="estimate in estimations"
                        v-bind:class="[
                            estimate === estimation ? 'active' : estimate.status === 'closed' ? 'list-group-item-success' : ''
                        ]"
                        @click="estimation = estimate; estimating = true"
                    >
                        Estimation for {{ estimate.task }}
                    </a>
                </div>
            </div>
        </div>

        <div class="action-box mt-2" v-if="user.id === session.user_id">
            <input v-if="!estimating" id="jira-task-id-input" class="form-control" type="text" placeholder="Jira Task Id" />
            <button v-if="!estimating" class="btn btn-outline-info" @click="startEstimation">
                Start estimation
            </button>
            <button v-if="estimating && estimation.status === 'open'" class="btn btn-outline-danger" @click="finishEstimation">
                Finish estimation
            </button>
        </div>

        <div class="estimation-board" v-if="estimating">
            <div class="py-5 mb-5 mx-auto bg-light rounded-3">
                <div class="container-fluid mb-5">
                    <div id="votes" class="votes">
                        <div class="wrapper" v-for="user in users">
                            <div class="vote-card ">
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
                    <div class="vote-card" data-value="1">
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

    </div>

</template>

<script>
export default {
    name: "EstimationComponent",

    props: [
        'user',
        'users',
        'session'
    ],

    data() {
        return {
            estimations: [],
            estimation: null,
            estimating: false,
            points: 5
        }
    },

    created() {
        this.getEstimations();

        Echo.join(`game-${this.session.hash_id}`)
            .listen('StartEstimationEvent', (event) => {
                if (event.userId === this.session.user_id) {
                    this.estimating = true;
                }
            })
            .listen('FinishEstimationEvent', (event) => {
                if (event.estimation.game.user_id === this.session.user_id) {
                    const index = this.estimations.findIndex(estimate => estimate.id === event.estimation.id);
                    this.estimations[index] = event.estimation;
                    this.estimation = null;
                    this.estimating = false;
                }
            })
    },

    methods: {
        async getEstimations() {
            const response = await axios.get(`/game/${this.session.hash_id}/estimation`);
            this.estimations = response.data;
        },
        async startEstimation() {
            const jiraTaskId = document.getElementById('jira-task-id-input').value;
            const response = await axios.post(`/game/${this.session.hash_id}/estimation`, {
                game_id: this.session.id,
                task: jiraTaskId
            });
            // TODO przerzucic na event socketowy
            this.estimation = response.data;
            this.estimations.push(response.data);
        },
        async finishEstimation() {
            await axios.post(`/game/${this.session.hash_id}/estimation/${this.estimation.id}/finish`, {
                'points': this.points
            });
        }
    }
}
</script>

<style scoped>

</style>
