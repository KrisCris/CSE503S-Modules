<template>
    <div class="table">
        <div class="noti">
            <el-alert :title="title" :type="canCreate" center show-icon :closable="false"> </el-alert>
        </div>
        <div class="noti">
            <el-alert v-show="errorMsg" :title="errorMsg" type="error" effect="dark" :closable="false" @click="errorMsg=''"> </el-alert>
            <el-alert v-show="successMsg" :title="successMsg" type="success" show-icon :closable="false" @click="successMsg=''"> </el-alert>
        </div>
        <table class="linkTable">
            <tr>
                <th>ShortURL</th>
                <th>URL</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <tr v-for="link in links" :key="link._id">
                <td>
                    <el-button type="primary" @click="copy(link.shortened)" plain >{{ link.shortened }}</el-button>
                </td>
                <td>
                    <el-button type="info" plain @click="copy(link.original)"> {{ link.OLink }}</el-button>
                   </td>
                <td>
                    <el-button :type="link.statusColor" plain>{{ link.status }}</el-button>
                </td>
                <td>
                    <el-button type="primary" @click="copy(link.shortened)" round>Copy</el-button>
                    <el-button type="success" @click="renew(link._id)" round>Renew</el-button>
                    <el-button type="danger" @click="del(link._id)" round>Delete</el-button>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
import { GET, POST, PUT, PATCH, DELETE } from "../requests.js";

export default {
    name: "LinksTable",
    props: ['username', 'maxLinks'],
    data() {
        return {
            baseUrl: "",
            errorMsg: "",
            links: [],
            successMsg:"",
        };
    },
    computed:{
        availability(){
            return this.maxLinks - this.links.length
        },
        canCreate(){
            return this.availability > 0 ? 'success' : 'warning'
        },
        title(){
            return this.username + ', you can create ' + this.availability + '/' + this.maxLinks + ' link(s)'
        }
    },
    methods:{
        copy(data){
            this.updateClipboard(data)
        },
        renew(id){
            PATCH("/links/"+id).then((res)=>{
                if(res.code==1){
                    this.successMsg="Renewed!"
                    this.fetchLinks()
                } else {
                    if (res.code == 0) {
                        this.$router.push({ name: "Auth" });
                    } else {
                        this.errorMsg = res.msg;
                    }
                }
            })
        },
        del(id){
            DELETE("/links/"+id).then((res)=>{
                if(res.code==1){
                    this.successMsg="Deleted!"
                    this.fetchLinks()
                } else {
                    if (res.code == 0) {
                        this.$router.push({ name: "Auth" });
                    } else {
                        this.errorMsg = res.msg;
                    }
                }
            })
        },
        fetchLinks(){
            GET("/links/").then((res) => {
                if (res.code == 1) {
                    this.links = res.data
                    for(let link of this.links){
                        link['status'] = link.expiry - parseInt(new Date().getTime()/1000) > 0 ? parseInt((link.expiry - new Date().getTime()/1000)/24/3600) + " Days" : "Expired"
                        link['statusColor'] = link['status'] == 'Expired' ? "warning" : "primary"
                        link['shortened'] = this.baseUrl + "/" + link['shortened']
                        link['OLink'] = link['original'].length > 30 ? link['original'].substring(0,30)+"..." : link['original']
                    }
                } else {
                    if (res.code == 0) {
                        this.$router.push({ name: "Auth" });
                    } else {
                        this.errorMsg = res.msg;
                    }
                }
            });
        },
        updateClipboard(newClip) {
            let that = this
            navigator.clipboard.writeText(newClip).then(function() {
                that.successMsg="Copied "+newClip
            }, function() {
                errorMsg="No permission to access clipboard!"
            });
        }
    },
    mounted() {
        let url = window.location.origin.toLowerCase();
        if (url.startsWith("https://")) {
            url = url.substring(8, url.length);
        } else if (url.startsWith("http://")) {
            url = url.substring(7, url.length);
        }
        this.baseUrl = url;

        // fetch actual data
        this.fetchLinks()
    },
};
</script>

<style>
.table .noti {
    width:100%;
    margin: 1rem auto;
}
.linkTable {
    background-color: white;
    padding: 1.5rem;
    border-radius: 1rem;
    width: 100%;
}
.linkTable th {
    padding: 0.3rem 1rem;
}
.linkTable td {
    padding: 0.3rem 1rem;
}
.linkTable button {
    margin: 0px 0rem;
}
</style>