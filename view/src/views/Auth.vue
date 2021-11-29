<template>
    <h1>AUTH PAGE</h1>
    <input type="text" v-model="username" />
    <input type="password" v-model="password" />
    <button @click="login">login</button>
    <button @click="register">register</button>
    <p>
        {{ userInfo }}
    </p>
    <button @click="getInfo">info</button>
</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";

export default {
    name: "Auth",
    data() {
        return {
            username: "",
            password: "",
            userInfo: undefined,
        };
    },
    methods: {
        login() {
            if (this.username && this.password) {
                POST("/users/login", {
                    username: this.username,
                    password: this.password,
                }).then((res) => {
                    console.log(res);
                });
            } else {
                alert("invalid input");
            }
        },
        register() {
            if (this.username && this.password) {
                POST("/users/register", {
                    username: this.username,
                    password: this.password,
                }).then((res) => {
                    console.log(res);
                });
            } else {
                alert("invalid input");
            }
        },

        getInfo(){
            GET('/links/').then((res)=>{
                this.userInfo = res.data
            })
        }
    },
};
</script>

<style>
</style>