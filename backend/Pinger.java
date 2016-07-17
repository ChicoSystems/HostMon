//to send to linux, commit, and push to remote.
//then go to /home/slack/hostmon and git pull origin
//to send to windows, commit, and push to remote.
//go to team, fetch from remote okay? okay?
import java.io.*;

/**
 * This Class implements an actual ping using the underlying operating systems
 * ping command. This file must know which operating system it is being ran on,
 * and make changes to the command sent to the os and changes to the way it parses
 * the information retrieved from the os.
 * @author Isaac Assegai
 */
public class Pinger {

	/**Constructor*/
	public Pinger() {
		//we don't need to do anything.
	}

	/**Entry Point for Testing the Class.*/
	public static void main(String args[]) {
		Pinger p = new Pinger();
		String cmdOutput = p.ping("google.com");
		System.out.println(cmdOutput);
	}

	/**
	 * Pings an Ip Address. Returns the latency in a String.
	 * @param ip The ip to ping.
	 * @return The latency.
	 */
	public String ping(String ip) {
		String pingCommand = getPingCommand(ip);
		String pingResult = "0";
		try {
			Runtime r = Runtime.getRuntime();
			Process p = r.exec(pingCommand);
			BufferedReader in = new BufferedReader(new InputStreamReader(
					p.getInputStream()));
			String inputLine;
			while ((inputLine = in.readLine()) != null) {
				pingResult += inputLine;
			}
			in.close();
			pingResult = pingResult.toLowerCase();
			if (pingResult.contains("request timed out")
					|| pingResult.contains("could not")
					|| pingResult.contains("100% packet loss")
					|| pingResult == null) {
				// don't know if request timed out is correct form in linux
				return "0";
			}
			pingResult = pingResult.substring(pingResult.indexOf("time=") + 5,
					pingResult.length() - 1);
			pingResult = pingResult.substring(0, pingResult.indexOf("ms"));
		} catch (Exception e) {
			System.err.println(e);
			pingResult = "0";
		}
		return pingResult;
	}

	/**
	 * Returns What the correct ping command is based on operating system.
	 * @param ip The ip to put into the command.
	 * @return The command with the ip integrated into it.
	 */
	private static String getPingCommand(String ip) {
		String pingCommand = null;
		String os = System.getProperty("os.name");
		os = os.toLowerCase();
		if (os.contains("windows")) {
			pingCommand = "ping " + ip + " -n 1";
		} else if (os.contains("linux")) {
			pingCommand = "ping " + ip + " -c 1";
		}
		return pingCommand;
	}
}
