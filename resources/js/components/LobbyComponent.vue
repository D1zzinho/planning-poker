<template>
    <div class="row">
        <div class="alert alert-success" v-if="message !== ''">{{ message }}</div>
        <button class="btn btn-outline-primary" @click="createGame">New game</button>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Session</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(game, index) in games" :key="index">
                    <th scope="col">{{ (index + 1) }}</th>
                    <td>Game-{{ new Date(game.created_at).getTime() }}</td>
                    <td>{{ game.status }}</td>
                    <td>
                        <a @click="openGame(game.hash_id)">Show</a>
                    </td>
                </tr>
            </tbody>
        </table>
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
            message: '',
            games: []
        }
    },

    created() {
        this.getGames();
    },

    methods: {
        async getGames() {
            const response = await axios.get('/lobby/user-games');
            this.games = response.data;
        },

        createGame() {
            axios.post('/lobby/create-game').then(result => {
                this.games.push(result.data.game);
                this.message = result.data.message;
            });

        },

        openGame(hashId) {
            window.location.href = `/game/${hashId}`;
        }
    }
}
</script>

<style scoped>

</style>
