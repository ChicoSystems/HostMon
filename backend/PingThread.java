import java.util.concurrent.PriorityBlockingQueue;

/**
 * This is a Thread in the Thread Pool. Each Thread will take a Runnable from
 * the queue and run it. After the thread runs, it will first find the amount of
 * time it took the runnable to complete, and add that to it's own total time.
 * It will track how many times it has run. It will give us an average time it
 * takes this thread to run a job. After it does this, it will check the
 * runnable to see if it is supposed to be run again, if so it will requeue it.
 * If not it will destroy it.
 * 
 * @author Isaac Assegai
 */
public class PingThread extends Thread {

	/**
	 * The Constructor.
	 * @param p The thread pool this object belongs to.
	 * @param queue The queue this object is going to help process.
	 * @param db The database this object is working with.
	 */
	public PingThread(ThreadPool p, PriorityBlockingQueue<RunnablePing> queue, DataBase db) {
		threadNumber = p.getThreads().size();
		this.db = db;
		parent = p;
		tracker = p.tracker;
		this.queue = queue;
		isStopped = false;
	}

	/*Public Methods.*/
	/**
	 * The main block of work this thread will do.
	 */
	public void run() {
		while(true){
			while(isStopped){
				try {
					Thread.sleep(1000);
				} catch (InterruptedException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
			while (!isStopped && !Thread.currentThread().isInterrupted()) {
				try {
					RunnablePing runnable = (RunnablePing) queue.take();
					setCurrentRunnable(runnable);
					runnable.run();
					// check if runnable is still active, requeue if so, destroy if not
					if(((RunnablePing)runnable).isActive()){
						queue.add(runnable);
					}else{
						//it'll get garbage collected by the jvm
					}
					printThreadStatus();
					//every 50 runs we will check if the parents average is meeting it's goals
					//if it is, we check by how much, if it's meeting it's goal by 2x then we get
					//rid of a thread. If it's not meeting it's goals we add a thread.
					updateThreadCount();
					setCurrentRunnable(null);
				} catch (InterruptedException consumed) {
					System.out.println("THREAD INTERRUPTED");
					// log or otherwise report exception,
					// but keep pool thread alive.
				}
				try {
					Thread.sleep(100);
				} catch (InterruptedException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
	}
	
	/**Prohibits the thread from processesing.*/
	public synchronized void stopThread() {
		isStopped = true;
	}
	
	/**Allows the thread to process.*/
	public synchronized void startThread() {
		isStopped = false;
	}

	/**Lets us know if the thread is running or not.*/
	public synchronized boolean isStopped() {
		return isStopped;
	}

	/**Tells us how many threads are running.*/
	public int getThreadNumber() {
		return threadNumber;
	}
	
	/**
	 * Returns the item this thread is currently processing, if any. Null if none.
	 * @return the currentRunnable
	 */
	public Runnable getCurrentRunnable() {
		return currentRunnable;
	}
	
	/*Private Methods */
	/**Prints the status of this thread in a table to the console.*/
	private void printThreadStatus() {
		String output = String.format("%15s", ((RunnablePing)currentRunnable).getIp());
		output += String.format("%8s", ((RunnablePing)currentRunnable).getLastLatency());
		output += String.format("%10s", "Thread #:");
		output += String.format("%2d", threadNumber);
		output += String.format("%6s", "Time:");
		output += String.format("%6d", tracker.getAverageCurrentTime());
		output += String.format("%16s", "Thread Running:");
		output += String.format("%3d", parent.getThreads().size());
		output += String.format("%6s", "Job#:");
		output += String.format("%3d", queue.size());
		output += String.format("%6s", "Run#:");
		output += String.format("%7d", tracker.getTotalRuns());
		output += String.format("%10s", "Run Time:");
		output += String.format("%7d", ((RunnablePing)currentRunnable).getRunTime());
		System.out.println(output);
	}

	/**
	 * Checks every x runs to see if we should add, or remove a thread.
	 */
	private void updateThreadCount() {
		if(tracker.getTotalRuns() %  Integer.parseInt(db.getConfig("runPerThreadCheck"))== 0){
			if(tracker.getAverageCurrentTime() != 0){ //fix for multiple threads doing this at once
				if(tracker.getAverageCurrentTime() <= Long.parseLong(db.getConfig("averageGoalTime"))){
					if((tracker.getAverageCurrentTime()*2) <= Long.parseLong(db.getConfig("averageGoalTime"))){
						//average run time is less than half goal run time
						Functions.debug("Exceeded Timing Goal, removing thread.");
						parent.removeThread(this);
					}else{
						//we are hitting our timing goals, do nothing.
						Functions.debug("Hit Timing Goal.");
					}
				}else if((tracker.getAverageCurrentTime()*1) > Long.parseLong(db.getConfig("averageGoalTime"))){
					//if Average Run Time is 2x bigger than goal run time, we add a thread
					Functions.debug("Missed Timing Goal, adding Thread");
					parent.addThread();
				}else{
					//missed timing goal, but we are not bad enough to start another thread.
					Functions.debug("Missed Timing Goal.");
				}
				//reset timers for parent, and me, and all brothers.
				tracker.resetCurrent();
			}
		}
	}
	
	/**
	 * Remembers the item this thread is currently processing so it can refer to it later.
	 * @param currentRunnable the currentRunnable to set
	 */
	private void setCurrentRunnable(Runnable currentRunnable) {
		this.currentRunnable = currentRunnable;
	}

	/* Field Objects & Variables */
	/**The Pool this thread belongs to.*/
	private ThreadPool parent;
	/**The queue this thread helps process.*/
	private PriorityBlockingQueue<RunnablePing> queue;
	/**Keeps track of if the thread is running.*/
	private boolean isStopped;
	/**Keeps track of the number of threads running.*/
	private int threadNumber;
	/**Keeps track of each jobs average time.*/
	private Tracker tracker;
	/**Keeps track of the job this thread is processing, null if none.*/
	private Runnable currentRunnable = null;
	/**The database this thread works with.*/
	private DataBase db;
}
