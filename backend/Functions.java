import java.io.File;

/**
 * Class used to aggregrate certain functions.
 * @author Isaac Assegai
 */
public class Functions {
	
	/**
	 * Print only if debug mode is true;
	 * @param msg The String to Print
	 */
	public static void debug(String msg){
		boolean dbug = true;
		if(dbug){
			System.out.println(msg);
		}
	}

	public static String getDBConfigFileName() {
		String filename = "cfg/db.cfg";
		File f = new File(filename);

	      if(!f.exists()){
	          filename = "../cfg/db.cfg";
	      }
		return filename;
	}
}
