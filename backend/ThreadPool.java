import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.PriorityBlockingQueue;

/**
 * The Thread Pool that processes the jobs in the queue. Threads are created or paused as
 * needed to accomplish timing goals.
 * @author Isaac Assegai
 * 
 */
public class ThreadPool {

	/**
	 * The Constructor
	 * @param queue The queue of jobs to process.
	 * @param noOfThreads The number of threads to begin with.
	 * @param t The tracker keeping track of job times.
	 * @param db The databse this class is working with.
	 */
	public ThreadPool(PriorityBlockingQueue<RunnablePing>queue, int noOfThreads, Tracker t, DataBase db) {
		this.tracker = t;
		this.queue = queue;
		this.db = db;
		for (int i = 0; i < noOfThreads; i++) {
			threads.add(new PingThread(this, queue, db));
		}
		for (PingThread thread : threads) {
			thread.start();
		}
	}
	
	/* Public Methods.*/
	/**
	 * Add another thread to the pool. If another thread already exists that is
	 * idle, we'll use that instead.
	 */
	public void addThread() {
		if(threads.size() < Integer.parseInt(db.getConfig("maxThreads"))){
			PingThread thread;
			if(stoppedThreads.size() > 0){
				//there is a thread we can use in stoppedThreads
				thread = stoppedThreads.remove(0);
				thread.startThread();
			}else{
				thread = new PingThread(this, queue, db);
				thread.start();
			}
			threads.add(thread);
		}
	}
	
	/**
	 * Stop a thread from processing and keep track of it.
	 * @param thread
	 */
	public void removeThread(PingThread thread){
		if(threads.size() > 1){
			threads.remove(thread);
			thread.stopThread();
			stoppedThreads.add(thread);
		}
	}

	/**
	 * Enqueue a Task a Thread will get to it.
	 */
	public synchronized void execute(RunnablePing task) {
		if (this.isStopped){
			throw new IllegalStateException("ThreadPool is stopped");
		}
		this.queue.add(task);
	}

	/**
	 * Stop All Threads
	 */
	public synchronized void stop() {
		this.isStopped = true;
		for (PingThread thread : threads) {
			thread.stopThread();
		}
	}
	
	/**
	 * Returns a list of all threads currently running.
	 * @return
	 */
	public synchronized ArrayList<PingThread> getThreads(){
		return (ArrayList<PingThread>) threads;
	}
	
	/* Private Methods */

	/* Field Objects & Variables */
	/**The queue of jobs this class is processing.*/
	private PriorityBlockingQueue<RunnablePing> queue = null;
	/**A List of active threads in this pool.*/
	private List<PingThread> threads = new ArrayList<PingThread>();
	/**A List of inactive threads in this pool.*/
	private List<PingThread> stoppedThreads = new ArrayList<PingThread>();
	/**Keeps track of if this pools is stopped or not.*/
	private boolean isStopped = false;
	/**The database this pool works with.*/
	private DataBase db;
	/**This keeps track of average job times.*/
	protected Tracker tracker;
}
