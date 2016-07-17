import java.util.ArrayList;
import java.util.Iterator;
import java.util.concurrent.PriorityBlockingQueue;

/**
 * Latency Checker is the Main Worker of the backend.
 * It will create a database connection getting an ArrayList of all
 * IP's that need to be checked. It will create a job for each ip. It will then start
 * passing jobs to it's thread pool to be executed.
 * Every time a job is executed it's results are written to the database, the average 
 * amount of time it took the job to be executed is calculated, if this average is
 * too high, another thread will be added to the thread pool.
 * @author Isaac Assegai
 */
public class LatencyChecker {
	
	/**
	 * The Constructor
	 * @param db The Database we are working with.
	 */
	public LatencyChecker(DataBase db){
		this.db = db;
		Functions.debug("LatencyChecker LatencyChecker()");
		init();
		mainLoop();
	}
	
	/* Public Methods */
	
	/* Private Methods */
	
	/**
	 * Loops every x seconds, Checks Database for list of ip's that are
	 * currently supposed to be active. Marks any job not in list as inactive. 
	 * Then checks every job currently owned by a thread. It checks those jobs against
	 * the databases active list and marks the one's not present as inactive
	 * Every time a job is done processing it checks if it is active or not,
	 * if not the job get's removed from the queue.
	 * Any job that is not present in the queue or worker threads, but is present
	 * in the active list will be created and added to the queue.
	 */
	private void mainLoop(){
		while(running){
			Functions.debug("LatencyChecker mainLoop()");
			//get a list of active ip's from the database
			ArrayList<String>activeIps = db.getActiveIps();
			ArrayList<RunnablePing>pingToRemove = new ArrayList<RunnablePing>();
			ArrayList<RunnablePing>activePings = new ArrayList<RunnablePing>();
			//loop through activeIP's compare them with ip's from runnables in queue, and pool
			for(int i = 0; i < activeIps.size(); i++){
				//go through queue and get all RunnablePings that math ActiveIP's
				Iterator queueIterator = queue.iterator();
				while(queueIterator.hasNext()){
					RunnablePing p = (RunnablePing) queueIterator.next();
					
					//queue.remove(p);
					if(p.getIp().equals(activeIps.get(i))){
						if(!activePings.contains(p)) {
							activePings.add(p);
						}
					}else{
						p.setActive(false);
					}
				}
				
				//next we loop through the thread pool threads, get their runnablePing and check that
				for(int j = 0; j < pool.getThreads().size(); j++){
					RunnablePing p = (RunnablePing) pool.getThreads().get(j).getCurrentRunnable();
					if(p != null){
						
						if(p.getIp().equals(activeIps.get(i))){
							if(!activePings.contains(p)){
								activePings.add(p);
							}
						}else{
							p.setActive(false);
						}
					}
				}
			}
			
			//next we loop through active pings and remove anything that doesn't have a activeIP
			queue.clear();
			for(int i = 0; i< activePings.size(); i++){
				boolean found = false;
				for(int j = 0; j < activeIps.size(); j++){
					if(activeIps.get(j).equals(activePings.get(i).getIp())){
						found = true;
					}
				}
				if(found){
					if(!queue.contains(activePings.get(i))){
						queue.add(activePings.get(i));
						activePings.get(i).setActive(true);
					}
				}
			}
			
			//any activeIp's left should be new one's that need to
			//be added to the priority queue
			for(int i = 0; i < activeIps.size(); i++){
				RunnablePing p = new RunnablePing(activeIps.get(i), queue, db, tracker);
				boolean addIt = true;
				Iterator queueIterator = queue.iterator();
				while(queueIterator.hasNext()){
					RunnablePing p2 = (RunnablePing) queueIterator.next();
					if(p2.getIp().equals(p.getIp())){ //if the ip's equal, we don't add it again
						addIt = false;
					}
				}
				if(addIt){
					queue.add(p);
				}
			}
			
			try {
				Thread.sleep(10000);
			} catch (InterruptedException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			System.out.print("latency checker");
			running = db.shouldBackendContinueRunning();
		}
		System.err.println("latency checker no longer running");
		db.informDBofShutdown();
		System.exit(0);
	}
	
	/**Initialize The Field Objects & Variables for the Class*/
	private void init() {
		Functions.debug("LatencyChecker init()");
		tracker = new Tracker();
		this.db = db;
		queue = new PriorityBlockingQueue();
		String sThreads = db.getConfig("startingThreads");
		int startingThreads = Integer.parseInt(sThreads);
		pool = new ThreadPool(queue, startingThreads, tracker, db);
		running = db.shouldBackendContinueRunning();
	}

	/* Field Objects & Variables */
	/**The Database we are working with.*/
	private DataBase db;
	/**The queue jobs are stored in.*/
	private PriorityBlockingQueue queue;
	/**The pool of threads that processes the queue.*/
	private ThreadPool pool;
	/**Is this class running.*/
	private boolean running;
	/**Keeps track of average job run times.*/
	private Tracker tracker;
}
