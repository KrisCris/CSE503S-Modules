<template>
    <Nav />
    <div id="table">
        <LinksTable :username="username" :maxLinks="maxLinks" />
    </div>
</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";
import Nav from "../components/Nav.vue";
import LinksTable from "../components/LinksTable.vue";

export default {
    name: "Links",
    components: { LinksTable, Nav },
    data() {
        return {
            username:'',
            maxLinks: 0
        };
    },
    mounted() {
        GET("/users/").then((res) => {
            if (res.code == 1) {
                this.username = res.data.username
                this.maxLinks = res.data.linksNum
            } else {
                if (res.code == -1) {
                    this.$router.push({ name: "Auth" });
                } else if (res.code == -3) {
                    POST("/user/refresh", {}).then(() => {
                        window.location.reload;
                    });
                }
            }
        });
    },
    methods: {},
};
</script>

<style>
#table {
    margin: auto;
    width: 80%;
    padding: 10px;
}
</style>