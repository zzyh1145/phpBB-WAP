<form action="{S_ACTION}" method="post">
{HIDDEN_THINGS}
<table width="100%" cellpadding="4" cellspacing="0" border="0" class="forumline">
	<tr>
		<td class="catSides"><span class="cattitle">{L_EDIT_TEMPLATE}</span>
</td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{FILE_NAME}</span></td>
  </tr>
	<tr>
		<td class="row1">
			<textarea name="file_data" rows="30" cols="75" class="post">{FILE_DATA}</textarea>
		</td>
	</tr>
	<tr>
		<td class="row1">
			<input type="submit" name="{SUBMIT_NAME}" value="{L_SUBMIT}" class="mainoption" /><br />
			<input type="reset" value="{L_RESET}" class="liteoption" />
		</td>
	</tr>
</table>
</form>
