import java.util.concurrent.PriorityBlockingQueue;

/**
 * This is a job that actually pings an ip address. It is processable by our ThreadPool.
 * @author Isaac Assegai
 */
public class RunnablePing  implements Comparable, Runnable{
	
	/**
	 * The constructor
	 * @param ip The address this job will ping.
	 * @param queue The queue this job will be added to.
	 * @param db The Database this job is working with.
	 * @param t The tracker used to keep track of each job's average run times.
	 */
	public RunnablePing(String ip, PriorityBlockingQueue<RunnablePing>queue, DataBase db, Tracker t){
		this.tracker = t;
		this.db = db;
		pinger = new Pinger();
		setActive(true);
		runTime = 0;
		timeCompletedLast = 0;
		this.ip = ip;
		this.queue	= queue;
	}
	
	/*Public Methods.*/
	/**The work done when the job is ran by a thread.*/
	@Override
	public void run() {
		long startTime = System.currentTimeMillis();
		String latency = pinger.ping(ip);
		setLastLatency(latency);
		String timeStamp = Long.toString(System.currentTimeMillis());
		db.recordPing(ip, timeStamp, latency);
		//if timeCompletedLast is 0, this is first run and runtime is calculated from start
		if(timeCompletedLast == 0){
			runTime = System.currentTimeMillis() - startTime;
		}else{
			// else, we measure runtime from end to end
			runTime = System.currentTimeMillis() - timeCompletedLast;
		}
		tracker.addPing(runTime);
		timeCompletedLast = System.currentTimeMillis();
		//we need to slow total job time down sometimes down.
		try {
			Thread.sleep(100);
		} catch (InterruptedException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Used by the queue to insert this into the correct place based on the last time completed.
	 */
	public int compareTo(Object o) {
		final int BEFORE = -1;
	    final int EQUAL = 0;
	    final int AFTER = 1;
	    int returnVal = 0;
	    RunnablePing p = (RunnablePing) o;
	    if(getTimeCompleted() < p.getTimeCompleted()){
	    	returnVal = BEFORE;
	    }else if(getTimeCompleted() > p.getTimeCompleted()){
	    	returnVal = AFTER;
	    }else{
	    	returnVal = EQUAL;
	    }
		return returnVal;
	}
	
	/**
	 * Reports the latest time it took to run this job.
	 * @return
	 */
	public long getRunTime(){
		return runTime;
	}
	
	/**
	 * Returns the IP of this job.
	 * @return
	 */
	public String getIp() {
		return ip;
	}

	/**
	 * Sets the IP of this job.
	 * @param ip
	 */
	public void setIp(String ip) {
		this.ip = ip;
	}
	
	/**@return the lastLatency*/
	public String getLastLatency() {
		return lastLatency;
	}

	/**@param lastLatency the lastLatency to set*/
	public void setLastLatency(String lastLatency) {
		this.lastLatency = lastLatency;
	}

	/**@return the active*/
	public boolean isActive() {
		return active;
	}

	/**
	 * @param active the active to set
	 */
	public void setActive(boolean active) {
		this.active = active;
	}
	
	/*Private Methods.*/
	/**
	 * Lets us know the time this job was last completed.
	 * @return
	 */
	private long getTimeCompleted(){
		return timeCompletedLast;
	}

	/* Field Objects & Variables */
	private long timeCompletedLast;
	private String ip;
	private PriorityBlockingQueue<RunnablePing> queue;
    private long runTime;
    private boolean active;
    private Pinger pinger;
    private DataBase db;
    private String lastLatency = "";
    private Tracker tracker;
}
