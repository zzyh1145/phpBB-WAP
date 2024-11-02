<form action="{S_ACTION}" method="post">
{HIDDEN_THINGS}
<table width="100%" cellpadding="4" cellspacing="0" border="0" class="forumline">
	<tr>
		<td class="catSides"><span class="cattitle">{L_CHOOSE_TEMPLATE}</span>
</td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{FILE_NAME}</span></td>
  </tr>
	<tr>
		<td class="row1">
		<select name="template" class="post">
		   {S_TEMPLATES}
    </select>
    </td>
	<tr>
		<td class="row1">
			<input type="submit" name="{SUBMIT_NAME}" value="{L_SUBMIT}" class="mainoption" />
		</td>
	</tr>
</table>
</form>
