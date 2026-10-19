import java.util.Scanner;

public class Task1 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input number of terms: ");
        int n = sc.nextInt();
        int odd = 1;
        int sum = 0;
        System.out.println("The Odd numbers are: ");

        for (int i = 0; i < n; i++)
        {
            System.out.println(odd);
            sum += odd;
            odd += 2;
        }
        System.out.println("The Sum of odd Natural Numbers up to " + n + " terms is: " + sum);
    }
}