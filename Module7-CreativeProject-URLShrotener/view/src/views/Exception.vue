<template>
  <h1>404 Page Not Found</h1>
  <el-button type="primary" round @click="$router.push({ name: 'Home' });">Back</el-button>
</template>

<script>
export default {

}
</script>

<style scoped>
h1{
  color:white;
}
</style>