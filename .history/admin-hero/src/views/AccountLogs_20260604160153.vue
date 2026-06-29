<el-table :data="logs" style="width: 100%">
  <el-table-column prop="stt" label="STT" width="60" />
  
  <el-table-column prop="player_name" label="Tên tài khoản" />
  
  <el-table-column prop="created_at" label="Thời gian tạo" />
  
  <el-table-column prop="creation_method" label="Nguồn tạo">
    <template #default="scope">
      <el-tag :type="scope.row.creation_method === 'Admin tạo' ? 'danger' : 'success'">
        {{ scope.row.creation_method }}
      </el-tag>
    </template>
  </el-table-column>
</el-table>