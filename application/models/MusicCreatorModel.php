<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MusicCreatorModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_artist($data)
  	{
  		$this->db->insert('user_music_creator',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function get_artist($music_creator_id = '')
  	{
  		$this->db->select('umc.iMusicCreatorid  as music_creator_id,umc.iUsersId as user_id,umc.vArtistName as artist_name,umc.dtAddedDate as added_date');
		$this->db->from('user_music_creator as umc');
		if(!empty($music_creator_id))
		{
			$this->db->where('umc.iMusicCreatorid ',$music_creator_id);	
		}
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}   

  	public function upload_music($music_creator_data,$music_creator_id)
  	{
  		$this->db->where('iMusicCreatorid', $music_creator_id);
		$result = $this->db->update('user_music_creator', $music_creator_data);
		return $result;
  	}

  	public function get_music_creator_details($music_creator_id = '')
  	{
  		$this->db->select("users.iUsersId as user_id,users.vName as name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vUploadMusic as music,user_music_creator.dtAddedDate as added_date,user_music_creator.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,");
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
		$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_music_creator.vCategories)<> 0","left",false);
		if(!empty($music_creator_id))
		{
			$this->db->where('users.iUsersId',$music_creator_id);
		}
		$this->db->where('users.iRoleId',2);
		$this->db->group_by('users.iUsersId');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}

  	public function delete_music_creator($music_creator_id = '')
  	{
  		$this->db->where('iUsersId', $music_creator_id);
    	$this->db->delete('user_music_creator');

    	$this->db->where('iUsersId', $music_creator_id);
    	$result = $this->db->delete('users');
    	return $result;
  	}

  	public function update_music_creator($music_creator_data,$music_creator_id)
  	{
  		
  		$this->db->where('iUsersId', $music_creator_id);
		$result = $this->db->update('user_music_creator', $music_creator_data);

		return $result;
  	}
}
