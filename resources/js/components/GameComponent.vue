<template>
    <div class="row">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Active users
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex" v-for="user in users">
                        {{ user.name }} <span v-if="user.id === session.user_id" class="text-info ml-auto">owner</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">

                </div>
                <div class="card-body">

                </div>
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
export default {
    props: [
        'user',
        'session'
    ],

    data() {
        return {
            isOwner: false,
            users: []
        }
    },

    created() {
        this.checkIfUserIsGameOwner();

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
        checkIfUserIsGameOwner() {
            this.isOwner = this.user.id === this.session.user_id;
        }
    }
}
</script>
