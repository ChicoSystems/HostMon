import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map.Entry;

/**
 * This class performs the periodic maintenance that the database requires to perform
 * in a round-robin type of way. maintenance happens through timers.
 * There are two types of timers, one type clears old information from db tables.
 * The other type averages out information in one db table and adds that average to another.
 * @author Isaac Assegai
 */
public class DBMaintainer extends Thread{
	
	/**
	 * Entry Point for Class Testing
	 * @param args
	 */
	public static void main(String[] args){
		DataBase db = new DataBase(DataBase.getDBOptions());
		DBMaintainer dbMaintainer = new DBMaintainer(db);
	}
	
	/**
	 * The Constructor
	 * @param db The database that this class will be working with.
	 */
	public DBMaintainer(DataBase db) {
		isRunning = db.shouldBackendContinueRunning();
		this.db = db;
		Functions.debug("DBMaintainer DBMaintainer()");
		start();
	}
	
	/* Public Methods */
	/**
	 * The main work done by this classes dedicated thread.
	 */
	public void run() {
		long minuteTimer = 0;
		ArrayList<HashMap<String, String>> timers = db.getTimers();
		try {
			long fiveMinuteTimer = Long.decode(timers.get(0).get("fiveMinuteTimer"));
			long fifteenMinuteTimer = Long.decode(timers.get(1).get("fifteenMinuteTimer"));
			long hourTimer = Long.decode(timers.get(2).get("hourTimer"));
			long twelveHourTimer = Long.decode(timers.get(3).get("twelveHourTimer"));
			long dayTimer = Long.decode(timers.get(4).get("dayTimer"));
			long fourtyEightHourTimer = Long.decode(timers.get(5).get("fourtyEightHourTimer"));
			long weekTimer = Long.decode(timers.get(6).get("weekTimer"));
			
			while(isRunning){
				if(System.currentTimeMillis() >= minuteTimer){
					//run every minute code & reset minute timer
					//get records older that 20 or x minutes, and delete them
					db.deleteOldMinuteRecords();
					minuteTimer = System.currentTimeMillis() + 60000;
				}
				
				if(System.currentTimeMillis() >= fiveMinuteTimer){
					//average the latest 5 minutes of data, for each device & reset minute timer
					ArrayList<HashMap<String, String>> newestFiveMinutesOfPings = db.getNewestFiveMinutesOfPings();
					HashMap<String, HashMap<String,ArrayList<String>>> hourRecords = new HashMap<String, HashMap<String, ArrayList<String>>>();
					for(int i = 0; i < newestFiveMinutesOfPings.size(); i++){
						String ip = newestFiveMinutesOfPings.get(i).get("ip");
						String time = newestFiveMinutesOfPings.get(i).get("time");
						String latency = newestFiveMinutesOfPings.get(i).get("latency");
						//if the hourRecords don't have the ip, we need to add a new record
						if(!hourRecords.containsKey(ip)){
							HashMap<String, ArrayList<String>> newRecord = new HashMap<String, ArrayList<String>>();
							//create new list for times and add the first one in
							ArrayList<String> timeList = new ArrayList<String>();
							timeList.add(time);
							newRecord.put("time", timeList);
							//create a new list for latencies, and add the first
							ArrayList<String> latencyList = new ArrayList<String>();
							latencyList.add(latency);
							newRecord.put("latency", latencyList);
							//add the time and latency to the ip label in the hourRecords
							hourRecords.put(ip, newRecord);
						}else{
							//hourRecords already contains a record for ip. We need to
							//add time and latency to their respective lists.
							HashMap<String, ArrayList<String>> modifyRecord = hourRecords.get(ip);
							//add the current time to timeList
							ArrayList<String>timeList = modifyRecord.get("time");
							timeList.add(time);
							//add the current latency to the latencyList
							ArrayList<String>latencyList = modifyRecord.get("latency");
							latencyList.add(latency);
						}
					}
					// now we are going to average out each list in hourRecords,
					// to get Rid of the list
					HashMap<String, HashMap<String, String>> averagedRecords = new HashMap<String, HashMap<String, String>>();
					for (Entry<String, HashMap<String, ArrayList<String>>> entry : hourRecords.entrySet()) {
						//System.out.printf("Key : %s and Value: %s %n", entry.getKey(), entry.getValue());
						String ip = entry.getKey();
						//average out times
						ArrayList<String>time = entry.getValue().get("time");
						long avgTime = 0;
						for(int i = 0; i < time.size(); i++){
							avgTime += Long.valueOf(time.get(i));
						}
						if(time.size() > 0)avgTime = avgTime / time.size();
						
						//average out latencies
						ArrayList<String>latency = entry.getValue().get("latency");
						long avgLatency = 0;
						for(int i = 0; i < latency.size(); i++){
							avgLatency += Long.valueOf(latency.get(i));
						}
						if(latency.size() > 0)avgLatency = avgLatency / latency.size();
						
						//add averaged record for this ip to averagedRecords
						HashMap<String, String> avgRecord = new HashMap<String, String>();
						avgRecord.put("time", String.valueOf(avgTime));
						avgRecord.put("latency", String.valueOf(avgLatency));
						averagedRecords.put(ip, avgRecord);
						//System.out.println("Added Entry to Hour Table");
					}
					db.recordHourRecords(averagedRecords);
					//System.out.println("stop here");
					long nextTime = System.currentTimeMillis() + 300000;
					db.setTimer("fiveMinuteTimer", String.valueOf(nextTime));
					fiveMinuteTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= fifteenMinuteTimer){
					//run every minute code & reset minute timer
					db.deleteOldHourRecords();
					long nextTime = System.currentTimeMillis() + 900000;
					db.setTimer("fifteenMinuteTimer", String.valueOf(nextTime));
					fifteenMinuteTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= hourTimer){
					//run every minute code & reset minute timer
					//average the latest 5 minutes of data, for each device & reset minute timer
					ArrayList<HashMap<String, String>> newestHourOfPings = db.getNewestHourOfPings();
					HashMap<String, HashMap<String,ArrayList<String>>> dayRecords = new HashMap<String, HashMap<String, ArrayList<String>>>();
					for(int i = 0; i < newestHourOfPings.size(); i++){
						String ip = newestHourOfPings.get(i).get("ip");
						String time = newestHourOfPings.get(i).get("time");
						String latency = newestHourOfPings.get(i).get("latency");
						//if the hourRecords don't have the ip, we need to add a new record
						if(!dayRecords.containsKey(ip)){
							HashMap<String, ArrayList<String>> newRecord = new HashMap<String, ArrayList<String>>();
							//create new list for times and add the first one in
							ArrayList<String> timeList = new ArrayList<String>();
							timeList.add(time);
							newRecord.put("time", timeList);
							//create a new list for latencies, and add the first
							ArrayList<String> latencyList = new ArrayList<String>();
							latencyList.add(latency);
							newRecord.put("latency", latencyList);
							//add the time and latency to the ip label in the hourRecords
							dayRecords.put(ip, newRecord);
						}else{
							//hourRecords already contains a record for ip. We need to
							//add time and latency to their respective lists.
							HashMap<String, ArrayList<String>> modifyRecord = dayRecords.get(ip);
							//add the current time to timeList
							ArrayList<String>timeList = modifyRecord.get("time");
							timeList.add(time);
							//add the current latency to the latencyList
							ArrayList<String>latencyList = modifyRecord.get("latency");
							latencyList.add(latency);
						}
					}
					// now we are going to average out each list in hourRecords,
					// to get Rid of the list
					HashMap<String, HashMap<String, String>> averagedRecords = new HashMap<String, HashMap<String, String>>();
					for (Entry<String, HashMap<String, ArrayList<String>>> entry : dayRecords.entrySet()) {
						//System.out.printf("Key : %s and Value: %s %n", entry.getKey(), entry.getValue());
						String ip = entry.getKey();
						//average out times
						ArrayList<String>time = entry.getValue().get("time");
						long avgTime = 0;
						for(int i = 0; i < time.size(); i++){
							avgTime += Long.valueOf(time.get(i));
						}
						if(time.size() > 0)avgTime = avgTime / time.size();
						
						//average out latencies
						ArrayList<String>latency = entry.getValue().get("latency");
						long avgLatency = 0;
						for(int i = 0; i < latency.size(); i++){
							avgLatency += Long.valueOf(latency.get(i));
						}
						if(latency.size() > 0)avgLatency = avgLatency / latency.size();
						
						//add averaged record for this ip to averagedRecords
						HashMap<String, String> avgRecord = new HashMap<String, String>();
						avgRecord.put("time", String.valueOf(avgTime));
						avgRecord.put("latency", String.valueOf(avgLatency));
						averagedRecords.put(ip, avgRecord);
					}
					//System.out.println("Added Entry to Day Table");
					db.recordDayRecords(averagedRecords);
					//System.out.println("stop here");
					long nextTime = System.currentTimeMillis() + 3600000;
					db.setTimer("hourTimer", String.valueOf(nextTime));
					hourTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= twelveHourTimer){
					//run every minute code & reset minute timer
					db.deleteOldDayRecords();
					long nextTime = System.currentTimeMillis() + 43200000;//12 hours
					db.setTimer("twelveHourTimer", String.valueOf(nextTime));
					twelveHourTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= dayTimer){
					//run every minute code & reset minute timer
					//average the latest 5 minutes of data, for each device & reset minute timer
					ArrayList<HashMap<String, String>> newestDayOfPings = db.getNewestDayOfPings();
					HashMap<String, HashMap<String,ArrayList<String>>> weekRecords = new HashMap<String, HashMap<String, ArrayList<String>>>();
					for(int i = 0; i < newestDayOfPings.size(); i++){
						String ip = newestDayOfPings.get(i).get("ip");
						String time = newestDayOfPings.get(i).get("time");
						String latency = newestDayOfPings.get(i).get("latency");
						//if the hourRecords don't have the ip, we need to add a new record
						if(!weekRecords.containsKey(ip)){
							HashMap<String, ArrayList<String>> newRecord = new HashMap<String, ArrayList<String>>();
							//create new list for times and add the first one in
							ArrayList<String> timeList = new ArrayList<String>();
							timeList.add(time);
							newRecord.put("time", timeList);
							//create a new list for latencies, and add the first
							ArrayList<String> latencyList = new ArrayList<String>();
							latencyList.add(latency);
							newRecord.put("latency", latencyList);
							//add the time and latency to the ip label in the hourRecords
							weekRecords.put(ip, newRecord);
						}else{
							//hourRecords already contains a record for ip. We need to
							//add time and latency to their respective lists.
							HashMap<String, ArrayList<String>> modifyRecord = weekRecords.get(ip);
							//add the current time to timeList
							ArrayList<String>timeList = modifyRecord.get("time");
							timeList.add(time);
							//add the current latency to the latencyList
							ArrayList<String>latencyList = modifyRecord.get("latency");
							latencyList.add(latency);
						}
					}
					// now we are going to average out each list in hourRecords,
					// to get Rid of the list
					HashMap<String, HashMap<String, String>> averagedRecords = new HashMap<String, HashMap<String, String>>();
					for (Entry<String, HashMap<String, ArrayList<String>>> entry : weekRecords.entrySet()) {
						//System.out.printf("Key : %s and Value: %s %n", entry.getKey(), entry.getValue());
						String ip = entry.getKey();
						//average out times
						ArrayList<String>time = entry.getValue().get("time");
						long avgTime = 0;
						for(int i = 0; i < time.size(); i++){
							avgTime += Long.valueOf(time.get(i));
						}
						if(time.size() > 0)avgTime = avgTime / time.size();
						
						//average out latencies
						ArrayList<String>latency = entry.getValue().get("latency");
						long avgLatency = 0;
						for(int i = 0; i < latency.size(); i++){
							avgLatency += Long.valueOf(latency.get(i));
						}
						if(latency.size() > 0)avgLatency = avgLatency / latency.size();
						
						//add averaged record for this ip to averagedRecords
						HashMap<String, String> avgRecord = new HashMap<String, String>();
						avgRecord.put("time", String.valueOf(avgTime));
						avgRecord.put("latency", String.valueOf(avgLatency));
						averagedRecords.put(ip, avgRecord);
					}
					//System.out.println("Added Entry to Week Table");
					db.recordWeekRecords(averagedRecords);
					//System.out.println("stop here");
					long nextTime = System.currentTimeMillis() + 86400000; //1day
					db.setTimer("dayTimer", String.valueOf(nextTime));
					dayTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= fourtyEightHourTimer){
					//run every minute code & reset minute timer
					db.deleteOldWeekRecords();
					long nextTime = System.currentTimeMillis() + 172800000; //48 hours
					db.setTimer("fourtyEightHourTimer", String.valueOf(nextTime));
					fourtyEightHourTimer = nextTime;
				}
				
				if(System.currentTimeMillis() >= weekTimer){
					//run every minute code & reset minute timer
					//average the latest 5 minutes of data, for each device & reset minute timer
					ArrayList<HashMap<String, String>> newestWeekOfPings = db.getNewestWeekOfPings();
					HashMap<String, HashMap<String,ArrayList<String>>> yearRecords = new HashMap<String, HashMap<String, ArrayList<String>>>();
					for(int i = 0; i < newestWeekOfPings.size(); i++){
						String ip = newestWeekOfPings.get(i).get("ip");
						String time = newestWeekOfPings.get(i).get("time");
						String latency = newestWeekOfPings.get(i).get("latency");
						//if the hourRecords don't have the ip, we need to add a new record
						if(!yearRecords.containsKey(ip)){
							HashMap<String, ArrayList<String>> newRecord = new HashMap<String, ArrayList<String>>();
							//create new list for times and add the first one in
							ArrayList<String> timeList = new ArrayList<String>();
							timeList.add(time);
							newRecord.put("time", timeList);
							//create a new list for latencies, and add the first
							ArrayList<String> latencyList = new ArrayList<String>();
							latencyList.add(latency);
							newRecord.put("latency", latencyList);
							//add the time and latency to the ip label in the hourRecords
							yearRecords.put(ip, newRecord);
						}else{
							//hourRecords already contains a record for ip. We need to
							//add time and latency to their respective lists.
							HashMap<String, ArrayList<String>> modifyRecord = yearRecords.get(ip);
							//add the current time to timeList
							ArrayList<String>timeList = modifyRecord.get("time");
							timeList.add(time);
							//add the current latency to the latencyList
							ArrayList<String>latencyList = modifyRecord.get("latency");
							latencyList.add(latency);
						}
					}
					// now we are going to average out each list in hourRecords,
					// to get Rid of the list
					HashMap<String, HashMap<String, String>> averagedRecords = new HashMap<String, HashMap<String, String>>();
					for (Entry<String, HashMap<String, ArrayList<String>>> entry : yearRecords.entrySet()) {
						//System.out.printf("Key : %s and Value: %s %n", entry.getKey(), entry.getValue());
						String ip = entry.getKey();
						//average out times
						ArrayList<String>time = entry.getValue().get("time");
						long avgTime = 0;
						for(int i = 0; i < time.size(); i++){
							avgTime += Long.valueOf(time.get(i));
						}
						if(time.size() > 0)avgTime = avgTime / time.size();
						
						//average out latencies
						ArrayList<String>latency = entry.getValue().get("latency");
						long avgLatency = 0;
						for(int i = 0; i < latency.size(); i++){
							avgLatency += Long.valueOf(latency.get(i));
						}
						if(latency.size() > 0)avgLatency = avgLatency / latency.size();
						
						//add averaged record for this ip to averagedRecords
						HashMap<String, String> avgRecord = new HashMap<String, String>();
						avgRecord.put("time", String.valueOf(avgTime));
						avgRecord.put("latency", String.valueOf(avgLatency));
						averagedRecords.put(ip, avgRecord);
					}
					//System.out.println("Added Entry to Year Table");
					db.recordYearRecords(averagedRecords);
					//System.out.println("stop here");
					long nextTime = System.currentTimeMillis() + 604800000; //1week
					db.setTimer("weekTimer", String.valueOf(nextTime));
					weekTimer = nextTime;
				}
				
				try {
					//let the thread sleep for a while.
					Thread.sleep(1000);
				} catch (InterruptedException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				//System.out.print("dbmaintainer");
				//isRunning = db.shouldBackendContinueRunning(); // Check if user has turned off backend
			}
			
		} catch (NumberFormatException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		}	
	}
	
	/* Private Methods */
	
	/* Field Objects & Variables */
	/**Keeps track if this class is running, setting this to false is a good way to shutdown class.*/
	private boolean isRunning;
	/**The DB this class is working with.*/
	private DataBase db;
}
