<template>
    <Nav />
    <div id="table">
        <LinksTable ref="linksTb" :username="username" :maxLinks="maxLinks" :inviteCode="inviteCode" @showEditor="initEditor"/>
    </div>
    <LinkEditor v-show="showEditor" @closeModal="showEditor=false" @updateURL="updatedUrl" ref="linkEd"/>
</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";
import Nav from "../components/Nav.vue";
import LinksTable from "../components/LinksTable.vue";
import LinkEditor from "../components/LinkEditor.vue"

export default {
    name: "Links",
    components: { LinksTable, Nav, LinkEditor },
    data() {
        return {
            username:'',
            maxLinks: 0,
            inviteCode:'',
            showEditor: false,
        };
    },
    mounted() {
        GET("/users/").then((res) => {
            if (res.code == 1) {
                this.username = res.data.username
                this.maxLinks = res.data.linksNum
                this.inviteCode = res.data.inviteCode
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
    methods: {
        updatedUrl(data){
            this.$refs.linksTb.updateModify(data)
            this.showEditor = false
        },
        initEditor(data){
            this.$refs.linkEd.setup(data)
            this.showEditor = true
        }
    },
};
</script>

<style>
#table {
    margin: auto;
    width: 80%;
    padding: 10px;
}
</style>