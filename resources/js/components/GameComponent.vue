<template>
    <div class="row">

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Active users
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item" v-for="user in users">{{ user.name }}</li>
                </ul>
            </div>
        </div>

        <estimation
            class="col-12"
            :user="user"
            :users="users"
            :session="session"
        ></estimation>

    </div>
</template>

<script>
import EstimationComponent from './EstimationComponent'

export default {
    props: [
        'user',
        'session'
    ],

    data() {
        return {
            users: []
        }
    },

    created() {
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

    mounted() {

    },

    methods: {

    }
}
</script>
