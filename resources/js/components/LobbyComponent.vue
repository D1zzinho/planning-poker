<template>

    <div class="row">

        <div class="col-12 mb-2">
            <b-breadcrumb :items="locations"></b-breadcrumb>

            <div class="pt-2 row">
                <div class="col-8 col-md-6">
                    <h1>Lobby</h1>
                    <p class="lead mb-0">Enter existing or create new planning room</p>
                </div>

                <div class="col-4 col-md-6 d-flex">
                    <button class="btn btn-outline-primary ml-auto my-auto" @click="createGame">New game</button>
                </div>
            </div>

        </div>

        <div class="col-12 mt-3">
            <div class="table-holder" v-if="gamesLoaded">
                <table class="table table-hover table-striped" v-if="games.length > 0">
                    <thead>
                        <tr style="font-size: 12px">
                            <th scope="col">Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Active est.</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game in games">
                            <td>Room #{{ game.id }}</td>
                            <td>{{ game.status }}</td>
                            <td>{{ getCountOfRoomActiveEstimations(game.estimations) }}</td>
                            <td>
                                <div v-if="game.status === 'active'">
                                    <b-button pill size="sm" v-b-tooltip.top
                                              title="Open room"
                                              @click="openGame(game.hash_id)"
                                              variant="success"
                                              class="m-2 m-md-0 mr-0 font-weight-bolder"
                                    >
                                        &#10132;
                                    </b-button>
                                    <b-button pill size="sm" v-b-tooltip.top
                                              title="Close room"
                                              @click="closeGame(game.hash_id)"
                                              variant="danger"
                                              class="m-2 m-md-0 ml-md-2 font-weight-bolder"
                                    >
                                        &#10005;
                                    </b-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <b-alert v-else variant="warning" show>You haven't created any game yet.</b-alert>
            </div>

            <div v-else class="my-3 d-flex w-100">
                <b-spinner class="m-auto" label="Loading..."></b-spinner>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "LobbyComponent",

    props: [
        'user'
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
                    active: true
                }
            ],
            games: [],
            gamesLoaded: false
        }
    },

    created() {
        this.getGames();
    },

    methods: {
        async getGames() {
            const response = await axios.get('/lobby/user-games');
            this.games = response.data;
            this.gamesLoaded = true;
        },

        async createGame() {
            const response = await axios.post('/lobby/create-game');
            this.games.unshift(response.data.game);
            this.makeToast(
                'Successfully created new game!',
                `It's now visible in your games list. Share its hash to allow other users to play!`,
                'success'
            );
        },

        async closeGame(gameHashId) {
            try {
                const response = await axios.patch(`/game/${gameHashId}`);
                this.makeToast(
                    'Success',
                    `Estimation room #${response.data.id} successfully closed!`,
                    'success'
                );
                await this.getGames();
            } catch (e) {
                this.makeToast(
                    'There was an error!',
                    `${e.response.data.message}!`,
                    'danger'
                );
            }
        },

        getCountOfRoomActiveEstimations(gameEstimations) {
            const activeAndFinishedEstimations = gameEstimations.filter(gameEstimation => {
                return gameEstimation.status === 'open' || gameEstimation.status === 'finished';
            });

            return activeAndFinishedEstimations.length;
        },

        openGame(hashId) {
            window.location.href = `/game/${hashId}`;
        },

        makeToast(title = '', message = '', type = null) {
            this.$bvToast.toast(message, {
                title: title,
                toaster: window.innerWidth > 991 ? 'b-toaster-bottom-right' : 'b-toaster-bottom-center',
                variant: type,
                solid: true,
                appendToast: true
            });
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

<style scoped>

</style>
