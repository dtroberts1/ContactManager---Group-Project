using System;
using System.Collections.Generic;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using Newtonsoft.Json;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;
using System.IO;

public partial class WISAAPI_SearchColors : System.Web.UI.Page
{

	public struct SearchColorsRequest
	{
		public string search;
	}

	public struct SearchColorsResponse
	{
		public string[] results;
		public string error;
	}

	protected void Page_Load(object sender, EventArgs e)
	{
		SearchColorsRequest req;
		SearchColorsResponse res = new SearchColorsResponse();
		res.error = String.Empty;
		res.results = new string[0];
		
		// 1. Deserialize the incoming Json.
		try
		{
			req = GetRequestInfo();
		}
		catch(Exception ex)
		{
			res.error = ex.Message.ToString();

			// Return the results as Json.
			SendResultInfoAsJson(res);

			return;
		}
		
		List<string> list = new List<string>();

		SqlConnection connection = new SqlConnection(ConfigurationManager.ConnectionStrings["ConnectionString"].ConnectionString);
		try
		{
			connection.Open();

			string sql = String.Format("select Name from Colors where Name like '%{0}%'", req.search);
			SqlCommand command = new SqlCommand( sql, connection );
			SqlDataReader reader = command.ExecuteReader();
			while( reader.Read() )
			{
				list.Add( Convert.ToString( reader["Name"] ) );
			}
			reader.Close();
			res.results = list.ToArray();
		}
		catch(Exception ex)
		{
			res.error = ex.Message.ToString();
		}
		finally
		{
			if( connection.State == ConnectionState.Open )
			{
				connection.Close();
			}
		}
		
		// Return the results as Json.
		SendResultInfoAsJson(res);
	}
	
	SearchColorsRequest GetRequestInfo()
	{
		// Get the Json from the POST.
		string strJson = String.Empty;
		HttpContext context = HttpContext.Current;
		context.Request.InputStream.Position = 0;
		using (StreamReader inputStream = new StreamReader(context.Request.InputStream))
		{
			strJson = inputStream.ReadToEnd();
		}

		// Deserialize the Json.
		SearchColorsRequest req = JsonConvert.DeserializeObject<SearchColorsRequest>(strJson);

		return (req);
	}
	
	void SendResultInfoAsJson(SearchColorsResponse res)
	{
		string strJson = JsonConvert.SerializeObject(res);
		Response.ContentType = "application/json; charset=utf-8";
		Response.Write(strJson);
		Response.End();
	}

}
