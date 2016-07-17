/**
 * Runnable Ping Reports to Tracker everytime it runs.
 * Tracker keeps track of totalRuns, totalTime, currentRuns, currentTime
 * for use in thread addition and subtraction
 * @author Isaac Assegai
 *
 */
public class Tracker {
	
	/** Constructor */
	public Tracker(){
		setTotalRuns(0);
		setTotalTime(0);
		setCurrentRuns(1);
		setCurrentTime(0);
	}
	
	/**
	 * Add a RunTime to the trackers average.
	 * @param time
	 */
	public synchronized void addPing(long time){
		setTotalRuns(getTotalRuns()+1);
		setTotalTime(getTotalTime()+time);
		setCurrentRuns(getCurrentRuns()+1);
		setCurrentTime(getCurrentTime()+time);
	}
	
	/**Reset the count of this tracker.*/
	public synchronized void resetCurrent(){
		setCurrentRuns(1);
		setCurrentTime(0);
	}
	
	/**@return the totalRuns*/
	public synchronized int getTotalRuns() {
		return totalRuns;
	}

	/**@param totalRuns the totalRuns to set*/
	public synchronized void setTotalRuns(int totalRuns) {
		this.totalRuns = totalRuns;
	}

	/**@return the totalTime*/
	public synchronized long getTotalTime() {
		return totalTime;
	}

	/**@param totalTime the totalTime to set*/
	public synchronized void setTotalTime(long totalTime) {
		this.totalTime = totalTime;
	}
	
	/**@return the currentRuns*/
	public synchronized int getCurrentRuns() {
		return currentRuns;
	}

	/**@param currentRuns the currentRuns to set*/
	public synchronized void setCurrentRuns(int currentRuns) {
		this.currentRuns = currentRuns;
	}

	/**@return the currentTime*/
	public synchronized long getCurrentTime() {
		return currentTime;
	}

	/**@param currentTime the currentTime to set*/
	public synchronized void setCurrentTime(long currentTime) {
		this.currentTime = currentTime;
	}
	
	/** Returns the average time. totals*/
	public synchronized long getAverageTotalTime(){
		return getTotalTime()/getTotalRuns();
	}
	
	/** returns the average current time.*/
	public synchronized long getAverageCurrentTime(){
		return getCurrentTime()/getCurrentRuns();
	}

	/* Field Variables and Objects */
	/**The total number of runs, doesn't get reset.*/
	private int totalRuns;
	/**The total run time, doesn't get reset.*/
	private long totalTime;
	/**The current number of runs since the last reset.*/
	private int currentRuns;
	/**The current runtime since last reset.*/
	private long currentTime;
}
