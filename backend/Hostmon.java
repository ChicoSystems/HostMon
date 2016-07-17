import java.util.ArrayList;
import java.util.HashMap;

/**
 * Java Back-End of the HostMon Project.
 * This program retrieves a list of ips to ping from a database.
 * It then implements a strategy to ping each ip within an allotted time.
 * @author Isaac Assegai
 */
public class Hostmon {

	/**The Program Start*/
	public static void main(String[] args) {
		Functions.debug("Hostmon main()");
		// TODO Auto-generated method stub
		Hostmon hostmon = new Hostmon();
	}
	
	/**The Constructor.*/
	public Hostmon(){
		Functions.debug("Hostmon Hostmon()");
		//Initialize Field Objects & Variables
		init(); 
	}
	
	/*Public Methods*/
	
	/*Private Methods */
	/**
	 * Initialize the Class
	 */
	private void init(){
		Functions.debug("Hostmon init()");
		//Retrieve the database connection options.
		HashMap<String, String> dbOptions = DataBase.getDBOptions();	
		//Instantiate the database object we will use to talk to the db.
		db = new DataBase(dbOptions);
		boolean alreadyRunning = db.backendAlreadyRunning();
		if(!alreadyRunning){ // Only start the program if it's determined we are not already running.
			// Tell the db we have started running.
			db.startRunning();
			//Instantiate the class that will be responsible for maintaining the db.
			dbMaintainer = new DBMaintainer(db);
			lChecker = new LatencyChecker(db);
		}else{ // We are already running, exit this program.
			System.err.println("Error, program is already running, or has ran within the last minute.");
			return;
		}
	}
	
	/*Field Objects & Variables*/
	
	/** The DataBase Object */
	private DataBase db;
	/** The DataBase Maintainer */
	private DBMaintainer dbMaintainer;
	/** The Main Latency Checker */
	private LatencyChecker lChecker;
}
