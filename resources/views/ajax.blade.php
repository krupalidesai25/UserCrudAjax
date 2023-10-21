<table>
    <thead>
      <tr>
        <th>Srno</th>
        <th>Select</th>
        <th>Name</th>
        <th>Contact No</th>
        <th>Hobbies</th>
        <th>Category</th>
        <th>Profile Pic</th>
        <th>Edit</th>
      </tr>
    </thead>
    <tbody>
    @forelse ($lists as $key => $row)
      <tr>
        <td>{{++$key}}</td>
        <td><input type="checkbox" class="bulk-checkbox" value="{{$row->id}}" name="ids[]"></td>
        <td class="editable" data-field="name">{{$row->name}}</td>
        <td class="editable" data-field="contact_no">{{$row->contact_no}}</td>
        <td class="editable" data-field="hobbies">{{implode(',',$row->hobbies->pluck('name')->toArray())}}</td>
        <td class="editable" data-field="category_id">{{$row->category->title}}</td>
        <td class="editable" data-field="profile_pic"><img width="60px" height="60px"  src="{{$row->profile_path}}" alt="{{$row->name}}"></td>
        <td class="editable" data-field="action"> 
            <a href="javascript:void(0);" class="editData" data-id="{{ $row->id }}">Edit</a>
            <a href="javascript:void(0);" class="deleteData" data-id="{{ $row->id }}">Delete</a>
        </td>
      </tr>
      @empty
      <tr>
          <td colspan="8" style="text-align: center">No matching records found</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  