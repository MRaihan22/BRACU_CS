import java.util.Scanner;

public class CW2 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("N = ");
        int n = sc.nextInt();
        int sum = 0;
        System.out.println("Input the " + n + " numbers: ");
        for (int i = 0; i < n; i++)
        {
            int nums = sc.nextInt();
            sum += nums;
        }

        double avg = (double) sum / n;

        System.out.println("The sum of " + n + " no is: " + sum);
        System.out.println("The Average is: " + avg);
    }
}
